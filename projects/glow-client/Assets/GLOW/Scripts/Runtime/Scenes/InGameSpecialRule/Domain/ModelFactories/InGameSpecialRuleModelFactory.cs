using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGameSpecialRule.Domain.ModelFactories
{
    public class InGameSpecialRuleModelFactory : IInGameSpecialRuleModelFactory
    {
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstEnemyOutpostDataRepository MstEnemyOutpostDataRepository { get; }
        [Inject] IMstStageEndConditionDataRepository MstStageEndConditionDataRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }

        public InGameSpecialRuleModel Create(InGameContentType contentType, MasterDataId targetMstId, QuestType questType)
        {
            if (targetMstId == MasterDataId.Empty)
            {
                return InGameSpecialRuleModel.Empty;
            }

            IMstInGameModel mstInGameModel = contentType == InGameContentType.Stage
                ? MstStageDataRepository.GetMstStage(targetMstId)
                : MstAdventBattleDataRepository.GetMstAdventBattleModel(targetMstId);

            // オブジェクト防衛
            var isDefenseTarget = !mstInGameModel.MstDefenseTargetId.IsEmpty()
                ? InGameSpecialRuleDefenseTargetFlag.True
                : InGameSpecialRuleDefenseTargetFlag.False;

            // ステージの場合の特別ルール
            var isEnemyOutpostDamageInvalidation = InGameSpecialRuleEnemyOutpostDamageInvalidationFlag.False;
            var isEnemyDestruction = InGameSpecialRuleEnemyDestructionFlag.False;
            var isSpecificEnemyDestruction = InGameSpecialRuleSpecificEnemyDestructionFlag.False;
            var enemyDestructionCount = DefeatEnemyCount.Empty;
            var specificEnemyDestructionTargetName = CharacterName.Empty;
            var specificEnemyDestructionCount = DefeatEnemyCount.Empty;
            var timeLimit = InGameSpecialRuleTimeLimit.Empty;

            if (contentType == InGameContentType.Stage)
            {
                // 敵ゲート破壊不可
                var mstEnemyOutpostModel = MstEnemyOutpostDataRepository.GetEnemyOutpost(mstInGameModel.MstEnemyOutpostId);

                isEnemyOutpostDamageInvalidation = mstEnemyOutpostModel.IsDamageInvalidation
                    ? InGameSpecialRuleEnemyOutpostDamageInvalidationFlag.True
                    : InGameSpecialRuleEnemyOutpostDamageInvalidationFlag.False;

                var mstStageEndConditions = MstStageEndConditionDataRepository.GetMstStageEndConditions(targetMstId);

                // 合計n体の敵を撃破すると勝利
                isEnemyDestruction = IsEnemyDestruction(mstStageEndConditions, out enemyDestructionCount);

                // 特定IDの敵を撃破すると勝利
                isSpecificEnemyDestruction = IsSpecificEnemyDestruction(
                    mstStageEndConditions,
                    out specificEnemyDestructionTargetName,
                    out specificEnemyDestructionCount);

                // 制限時間による勝利・敗北
                timeLimit = TranslateTimeLimit(mstStageEndConditions);
            }

            // メインクエストのスピードアタックは特別ルール扱いしない
            var mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository
                .GetInGameSpecialRuleModels(targetMstId, contentType)
                .Where(mst => !(mst.RuleType == RuleType.SpeedAttack && questType == QuestType.Normal))
                .ToList();

            if (mstInGameSpecialRuleModels.IsEmpty() &&
                !isDefenseTarget &&
                !isEnemyOutpostDamageInvalidation &&
                !isEnemyDestruction &&
                !isSpecificEnemyDestruction &&
                timeLimit.IsEmpty())
            {
                return InGameSpecialRuleModel.Empty;
            }

            var isSpeedAttack = mstInGameSpecialRuleModels.Any(mst => mst.RuleType == RuleType.SpeedAttack);

            var existsFormationRule = new InGameSpecialRuleExistFormationRuleFlag(mstInGameSpecialRuleModels.Any(
                mst => mst.RuleType is
                    RuleType.PartyUnitNum or
                    RuleType.PartyRarity or
                    RuleType.PartySeries or
                    RuleType.PartyAttackRangeType or
                    RuleType.PartyRoleType or
                    RuleType.PartyColor or
                    RuleType.PartySummonCostUpperEqual or
                    RuleType.PartySummonCostLowerEqual));

            var existsOtherRule = new InGameSpecialRuleExistOtherRuleFlag(
                isDefenseTarget ||
                isEnemyDestruction ||
                isSpecificEnemyDestruction ||
                isEnemyOutpostDamageInvalidation ||
                isSpeedAttack ||
                mstInGameSpecialRuleModels.Any(mst => mst.RuleType is RuleType.OutpostHp or RuleType.NoContinue));

            return new InGameSpecialRuleModel(
                TranslateSeriesLogImageAssetPathList(mstInGameSpecialRuleModels, MstSeriesDataRepository.GetMstSeriesModels()),
                TranslateRarities(mstInGameSpecialRuleModels),
                TranslateUnitRoleTypes(mstInGameSpecialRuleModels),
                TranslateUnitColors(mstInGameSpecialRuleModels),
                TranslateUnitAmount(mstInGameSpecialRuleModels),
                TranslateAttackRangeTypes(mstInGameSpecialRuleModels),
                enemyDestructionCount,
                specificEnemyDestructionTargetName,
                specificEnemyDestructionCount,
                TranslateStartOutpostHp(mstInGameSpecialRuleModels),
                timeLimit,
                isDefenseTarget,
                isEnemyDestruction,
                isSpecificEnemyDestruction,
                new InGameSpecialRuleStartOutpostHpFlag(mstInGameSpecialRuleModels.Any(mst => mst.RuleType == RuleType.OutpostHp)),
                isEnemyOutpostDamageInvalidation,
                new InGameSpecialRuleNoContinueFlag(mstInGameSpecialRuleModels.Any(mst => mst.RuleType == RuleType.NoContinue)),
                new InGameSpecialRuleSpeedAttackFlag(isSpeedAttack),
                existsFormationRule,
                existsOtherRule);
        }

        List<SeriesLogoImagePath> TranslateSeriesLogImageAssetPathList(
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels,
            IReadOnlyList<MstSeriesModel> mstSeriesModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartySeries)
                .Select(model => model.RuleValue.ToSeriesId())
                .Select(id => mstSeriesModels.Find(mst => mst.Id == id))
                .Select(mst => new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mst.SeriesAssetKey.Value)))
                .ToList();
        }

        List<Rarity> TranslateRarities(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartyRarity)
                .Select(model => model.RuleValue.ToRarity())
                .ToList();
        }

        List<CharacterUnitRoleType> TranslateUnitRoleTypes(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartyRoleType)
                .Select(model => model.RuleValue.ToUnitRoleType())
                .ToList();
        }

        List<CharacterColor> TranslateUnitColors(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartyColor)
                .Select(model => model.RuleValue.ToCharacterColor())
                .ToList();
        }

        InGameSpecialRuleUnitAmount TranslateUnitAmount(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            var mstStageEventRule = mstInGameSpecialRuleModels.FirstOrDefault(
                mst => mst.RuleType == RuleType.PartyUnitNum,
                MstInGameSpecialRuleModel.Empty);

            if (mstStageEventRule.IsEmpty())
            {
                return InGameSpecialRuleUnitAmount.Zero;
            }
            return mstStageEventRule.RuleValue.ToUnitAmount();
        }

        List<CharacterAttackRangeType> TranslateAttackRangeTypes(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartyAttackRangeType)
                .Select(model => model.RuleValue.ToAttackRangeType())
                .ToList();
        }

        InGameSpecialRuleStartOutpostHp TranslateStartOutpostHp(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            var mstStageEventRule = mstInGameSpecialRuleModels.FirstOrDefault(
                mst => mst.RuleType == RuleType.OutpostHp,
                MstInGameSpecialRuleModel.Empty);

            if (mstStageEventRule.IsEmpty())
            {
                return InGameSpecialRuleStartOutpostHp.Zero;
            }
            return mstStageEventRule.RuleValue.ToStartOutpostHp();
        }

        InGameSpecialRuleEnemyDestructionFlag IsEnemyDestruction(
            IReadOnlyList<MstStageEndConditionModel> mstStageEndConditions,
            out DefeatEnemyCount enemyDestructionCount)
        {
            var mstStageEndCondition = mstStageEndConditions.FirstOrDefault(
                model => model.ConditionType == StageEndConditionType.DefeatedEnemyCount,
                MstStageEndConditionModel.Empty);

            if (mstStageEndCondition.IsEmpty())
            {
                enemyDestructionCount = DefeatEnemyCount.Empty;
                return InGameSpecialRuleEnemyDestructionFlag.False;
            }

            enemyDestructionCount = mstStageEndCondition.ConditionValue1.ToDefeatEnemyCount();
            return InGameSpecialRuleEnemyDestructionFlag.True;
        }

        InGameSpecialRuleSpecificEnemyDestructionFlag IsSpecificEnemyDestruction(
            IReadOnlyList<MstStageEndConditionModel> mstStageEndConditions,
            out CharacterName specificEnemyDestructionTargetName,
            out DefeatEnemyCount specificEnemyDestructionCount)
        {
            var mstStageEndCondition = mstStageEndConditions.FirstOrDefault(
                model => model.ConditionType == StageEndConditionType.DefeatUnit,
                MstStageEndConditionModel.Empty);

            if (mstStageEndCondition.IsEmpty())
            {
                specificEnemyDestructionTargetName = CharacterName.Empty;
                specificEnemyDestructionCount = DefeatEnemyCount.Empty;
                return InGameSpecialRuleSpecificEnemyDestructionFlag.False;
            }

            // 敵キャラ名を取得する
            var mstEnemyCharacterModel = MstEnemyCharacterDataRepository.GetEnemyCharacter(
                mstStageEndCondition.ConditionValue1.ToMasterDataId());

            specificEnemyDestructionTargetName = mstEnemyCharacterModel.Name;

            // 撃破数
            specificEnemyDestructionCount = mstStageEndCondition.ConditionValue2.ToDefeatEnemyCount();

            return InGameSpecialRuleSpecificEnemyDestructionFlag.True;
        }

        InGameSpecialRuleTimeLimit TranslateTimeLimit(IReadOnlyList<MstStageEndConditionModel> mstStageEndConditions)
        {
            var mstStageEndCondition =
                mstStageEndConditions.FirstOrDefault(mst => mst.ConditionType == StageEndConditionType.TimeOver,
                    MstStageEndConditionModel.Empty);
            if (mstStageEndCondition.IsEmpty()) return InGameSpecialRuleTimeLimit.Empty;

            return new InGameSpecialRuleTimeLimit(
                mstStageEndCondition.ConditionValue1.ToTimeLimit(),
                mstStageEndCondition.StageEndType);
        }
    }
}
