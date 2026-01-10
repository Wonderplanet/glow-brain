using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.HomePartyFormation.Domain.Evaluators
{
    public class RecommendPartyFormationEvaluator : IRecommendPartyFormationEvaluator
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IMstEventBonusUnitDataRepository MstQuestEventBonusDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleUnitStatusDataRepository MstInGameSpecialRuleUnitStatusDataRepository { get; }
        [Inject] IMstQuestBonusUnitRepository MstQuestBonusUnitRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IAutoPlayerSequenceModelFactory AutoPlayerSequenceModelFactory { get; }

        public List<UserUnitModel> GetRecommendPartyFormationUnits(
            EventBonusGroupId eventBonusGroupId,
            MasterDataId mstSpecialRuleTargetId,
            InGameContentType contentType,
            MasterDataId enhanceQuestId,
            PartyMemberSlotCount partyMemberSlotCount)
        {
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            //イベントボーナスにまつわるもの
            var eventBonuses = MstQuestEventBonusDataRepository.GetEventBonuses(eventBonusGroupId);

            List<MstEnemyStageParameterModel> bossEnemies = new List<MstEnemyStageParameterModel>();
            List<MstEnemyStageParameterModel> normalEnemies = new List<MstEnemyStageParameterModel>();
            if (!mstSpecialRuleTargetId.IsEmpty())
            {
                AutoPlayerSequenceModel autoPlayerSequenceModel = AutoPlayerSequenceModel.Empty;
                if (contentType == InGameContentType.Stage)
                {
                    var mstStage = MstStageDataRepository.GetMstStage(mstSpecialRuleTargetId);
                    autoPlayerSequenceModel = AutoPlayerSequenceModelFactory.Create(mstStage.MstAutoPlayerSequenceSetId);
                }
                else
                {
                    var mstAdventBattle = MstAdventBattleDataRepository.GetMstAdventBattleModelFirstOrDefault(mstSpecialRuleTargetId);
                    autoPlayerSequenceModel = AutoPlayerSequenceModelFactory.Create(mstAdventBattle.MstAutoPlayerSequenceSetId);
                }

                var enemyList = autoPlayerSequenceModel.SummonEnemies;
                bossEnemies = enemyList.Where(enemy => enemy.IsBoss).ToList();
                normalEnemies = enemyList.Where(enemy => enemy.IsNormal).ToList();
            }

            var enhanceQuestBonusUnits = MstQuestBonusUnitRepository.GetQuestBonusUnits(enhanceQuestId)
                .Where(mst => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mst.StartAt, mst.EndAt))
                .ToList();

            var inGameSpecialRules = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                mstSpecialRuleTargetId,
                contentType);

            var resultUnits = new List<UserUnitModel>(userUnits);
            // まずは特殊ルールを満たすキャラを抽出
            resultUnits = GetAchievedSpecialRuleUnits(resultUnits, mstSpecialRuleTargetId, inGameSpecialRules);

            // 特殊ルールのステータス上昇を取得
            var groupIdList = inGameSpecialRules
                .Where(m => m.RuleType == RuleType.UnitStatus)
                .Select(m => m.RuleValue.ToMasterDataId())
                .Distinct()
                .ToList();
            var unitStatusModels = MstInGameSpecialRuleUnitStatusDataRepository
                .GetInGameSpecialRuleUnitStatusModels(groupIdList);

            // おまかせ編成対象キャラを並び替えて取得
            resultUnits = resultUnits
                // 1. イベントボーナスが高い順にソート
                .OrderByDescending(unit => GetBonusPercentage(eventBonuses, enhanceQuestBonusUnits, unit.MstUnitId))
                // 2. 有利キャラを前に持ってくる
                .ThenByDescending(unit => IsAdvantageEnemy(unit, bossEnemies, normalEnemies))
                // 3. 攻撃力と体力の合計でソート
                .ThenByDescending(unit =>
                {
                    var mstUnit = MstCharacterDataRepository.GetCharacter(unit.MstUnitId);
                    var unitStatus = UnitStatusCalculateHelper.Calculate(mstUnit, unit.Level, unit.Rank, unit.Grade);
                    var unitStatusWithSpecialRule = UnitStatusCalculateHelper.CalculateStatusWithSpecialRule(
                        unitStatus,
                        mstUnit.Id,
                        unitStatusModels);
                    return unitStatusWithSpecialRule.AttackPower.Value + unitStatusWithSpecialRule.HP.Value;
                })
                // 最後一致したらIDでソート
                .ThenBy(unit => unit.MstUnitId)
                .ToList();

            var partyUnitNumRule = inGameSpecialRules
                .FirstOrDefault(mst => mst.RuleType == RuleType.PartyUnitNum, MstInGameSpecialRuleModel.Empty);
            if (!partyUnitNumRule.IsEmpty())
            {
                var unitNum = partyUnitNumRule.RuleValue.ToUnitAmount();
                var slotCount = unitNum.Value >= partyMemberSlotCount.Value ? (int)partyMemberSlotCount.Value : unitNum.Value;

                // SlotCountが溢れる場合際Listを減らす
                if (resultUnits.Count <= slotCount) return resultUnits;

                var filteredUnits = resultUnits.Take(slotCount).ToList();
                resultUnits = AddSpecialCharacter(
                    resultUnits,
                    filteredUnits,
                    eventBonuses,
                    enhanceQuestBonusUnits,
                    partyMemberSlotCount);
            }
            else
            {
                // SlotCountが溢れる場合際Listを減らす
                if (resultUnits.Count <= partyMemberSlotCount.Value) return resultUnits;

                var filteredUnits = resultUnits.Take(partyMemberSlotCount.Value).ToList();
                resultUnits = AddSpecialCharacter(
                    resultUnits,
                    filteredUnits,
                    eventBonuses,
                    enhanceQuestBonusUnits,
                    partyMemberSlotCount);
            }

            return resultUnits;
        }

        List<UserUnitModel> GetAchievedSpecialRuleUnits(
            List<UserUnitModel> userUnits,
            MasterDataId mstSpecialRuleTargetId,
            IReadOnlyList<MstInGameSpecialRuleModel> inGameSpecialRules)
        {
            if (mstSpecialRuleTargetId.IsEmpty())
            {
                return userUnits;
            }

            if (inGameSpecialRules.IsEmpty())
            {
                return userUnits;
            }

            return userUnits.Where(unit => IsAchievedSpecialRule(unit, inGameSpecialRules)).ToList();
        }

        bool IsAchievedSpecialRule(UserUnitModel userUnitModel, IReadOnlyList<MstInGameSpecialRuleModel> inGameSpecialRules)
        {
            return InGameSpecialRuleAchievingEvaluator.IsAchievedSpecialRule(
                MstCharacterDataRepository.GetCharacter(userUnitModel.MstUnitId),
                inGameSpecialRules);
        }

        EventBonusPercentage GetBonusPercentage(
            IReadOnlyList<MstEventBonusUnitModel> eventBonuses,
            IReadOnlyList<MstQuestBonusUnitModel> enhanceQuestBonusUnits,
            MasterDataId mstUnitId)
        {
            var bonus = eventBonuses.FirstOrDefault(bonus => bonus.MstUnitId == mstUnitId,
                MstEventBonusUnitModel.Empty);
            if (!bonus.IsEmpty()) return bonus.BonusPercentage;

            var enhanceQuestBonusUnit = enhanceQuestBonusUnits.FirstOrDefault(enhance => enhance.MstUnitId == mstUnitId,
                MstQuestBonusUnitModel.Empty);
            return enhanceQuestBonusUnit.CoinBonusRate.ToEventBonusPercentage();
        }

        List<UserUnitModel> AddSpecialCharacter(
            List<UserUnitModel> userUnits,
            List<UserUnitModel> filteredUnits,
            IReadOnlyList<MstEventBonusUnitModel> eventBonuses,
            IReadOnlyList<MstQuestBonusUnitModel> enhanceQuestBonusUnits,
            PartyMemberSlotCount partyMemberSlotCount)
        {
            // すでにスペシャルキャラがいる場合は追加しない
            if (filteredUnits.Any(unit => MstCharacterDataRepository.GetCharacter(unit.MstUnitId).IsSpecialUnit))
            {
                return filteredUnits;
            }

            var specialCharacter = GetHighestPowerSpecialCharacter(userUnits);
            if (specialCharacter.IsEmpty()) return filteredUnits;

            for (int i = 0; i < filteredUnits.Count; i++)
            {
                if (i != partyMemberSlotCount.Value - 1) continue;

                // イベントボーナスがあるキャラを優先するため、イベントボーナスがある場合は追加しない
                if (GetBonusPercentage(eventBonuses, enhanceQuestBonusUnits, specialCharacter.MstUnitId).Value > 0) continue;

                // スペシャルキャラがいない場合はfilteredUnitsの最後のスロットに特殊キャラ入れ替え
                filteredUnits[i] = specialCharacter;
            }

            return filteredUnits;
        }

        UserUnitModel GetHighestPowerSpecialCharacter(List<UserUnitModel> userUnits)
        {
            var userUnitAndMsts = userUnits
                .Join(
                    MstCharacterDataRepository.GetCharacters(),
                    u => u.MstUnitId,
                    m => m.Id,
                    (u, m) => new { userUnit = u, mstCharacter = m });

            return userUnitAndMsts
                .Where(uAndm => uAndm.mstCharacter.IsSpecialUnit)
                .OrderByDescending(userUnit =>
                {
                    var unitStatus = UnitStatusCalculateHelper.Calculate(
                        userUnit.mstCharacter,
                        userUnit.userUnit.Level,
                        userUnit.userUnit.Rank,
                        userUnit.userUnit.Grade);
                    return unitStatus.AttackPower;
                })
                .Select(u => u.userUnit)
                .FirstOrDefault(UserUnitModel.Empty);
        }

        bool IsAdvantageEnemy(
            UserUnitModel userUnit,
            List<MstEnemyStageParameterModel> bossEnemies,
            List<MstEnemyStageParameterModel> normalEnemies)
        {
            // ボスがいるかどうか
            if (bossEnemies.Count == 0)
            {
                // ボスがいない場合は通常の敵を優先
                if (normalEnemies.Count == 0) return true;

                return IsAdvantageEnemy(userUnit, normalEnemies);
            }

            // ボスがいる場合はボスを優先
            return IsAdvantageEnemy(userUnit, bossEnemies);
        }

        bool IsAdvantageEnemy(UserUnitModel userUnit, List<MstEnemyStageParameterModel> enemies)
        {
            // 有利キャラである敵がいるかどうか
            var mstCharacter = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
            return enemies.Any(enemy => mstCharacter.Color.IsAdvantage(enemy.Color));
        }
    }
}
