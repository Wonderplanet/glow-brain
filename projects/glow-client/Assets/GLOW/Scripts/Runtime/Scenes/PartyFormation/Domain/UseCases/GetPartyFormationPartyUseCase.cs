using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PartyFormation.Domain.Models;
using GLOW.Scenes.PartyFormation.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class GetPartyFormationPartyUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEventBonusUnitDataRepository MstEventBonusUnitDataRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleUnitStatusDataRepository MstInGameSpecialRuleUnitStatusDataRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }
        [Inject] IInGameSpecialRuleUnitStatusEvaluator InGameSpecialRuleUnitStatusEvaluator { get; }
        [Inject] IMstUnitEncyclopediaEffectDataRepository MstUnitEncyclopediaEffectDataRepository { get; }
        [Inject] IMstUnitEncyclopediaRewardDataRepository MstUnitEncyclopediaRewardDataRepository { get; }
        [Inject] IInGameEventBonusUnitEffectProvider InGameEventBonusUnitEffectProvider { get; }
        [Inject] TotalPartyStatusCalculator TotalPartyStatusCalculator { get; }

        public PartyFormationPartyModel GetModel(
            PartyNo partyNo,
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType,
            UnitSortFilterCacheType cacheType,
            EventBonusGroupId eventBonusGroupId)
        {
            var party = PartyCacheRepository.GetCacheParty(partyNo);
            var bonusUnits = PartyCacheRepository.GetBonusUnits();

            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;

            var sortType = UnitSortFilterCacheRepository.GetSortType(cacheType);

            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels = new List<MstInGameSpecialRuleModel>();
            if (!specialRuleTargetMstId.IsEmpty())
            {
                mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                    specialRuleTargetMstId,
                    specialRuleContentType);
            }

            var partyRarities = GetAndTranslateRarities(
                GetMstStageEventRuleModels(mstInGameSpecialRuleModels, RuleType.PartyRarity));
            var seriesIds = GetAndTranslateSeriesIds(
                GetMstStageEventRuleModels(mstInGameSpecialRuleModels, RuleType.PartySeries));
            var attackRangeTypes = GetAndTranslateAttackRangeTypes(
                GetMstStageEventRuleModels(mstInGameSpecialRuleModels, RuleType.PartyAttackRangeType));
            var unitRoleTypes = GetAndTranslateUnitRoleTypes(
                GetMstStageEventRuleModels(mstInGameSpecialRuleModels, RuleType.PartyRoleType));
            var characterColors = GetAndTranslateUnitColor(
                GetMstStageEventRuleModels(mstInGameSpecialRuleModels, RuleType.PartyColor));
            var partyUnitNums = GetAndTranslatePartyMemberNums(
                GetMstStageEventRuleModels(mstInGameSpecialRuleModels, RuleType.PartyUnitNum));

            var groupIdList = mstInGameSpecialRuleModels
                .Where(m => m.RuleType == RuleType.UnitStatus)
                .Select(m => m.RuleValue.ToMasterDataId())
                .Distinct()
                .ToList();

            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels = new List<MstInGameSpecialRuleUnitStatusModel>();
            // PVP以外でのUnitStatusはステージギミック扱いのため空にする
            if (specialRuleContentType == InGameContentType.Pvp)
            {
                specialRuleUnitStatusModels =
                    MstInGameSpecialRuleUnitStatusDataRepository
                        .GetInGameSpecialRuleUnitStatusModels(groupIdList);
            }

            var members = party.GetUnitList()
                .Select((m, i) => TranslatePartyMemberModel(
                    m,
                    userUnitModels,
                    bonusUnits,
                    mstInGameSpecialRuleModels,
                    partyRarities,
                    seriesIds,
                    attackRangeTypes,
                    unitRoleTypes,
                    characterColors,
                    sortType,
                    specialRuleUnitStatusModels))
                .ToList();

            // パーティに編成されているユニットのデータを取得
            var unitDataList = party.GetUnitList()
                .Where(unit => !unit.IsEmpty())
                .Select(unit =>
                {
                    var userUnit = userUnitModels.Find(model => model.UsrUnitId.Value == unit.Value);
                    var mstCharacter = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
                    return (userUnit, mstCharacter);
                })
                .ToList();

            // 各ユニットのステータスを計算
            var unitStatusesList = unitDataList
                .Select(data =>
                {
                    var calculateStatus = UnitStatusCalculateHelper.Calculate(
                        data.mstCharacter,
                        data.userUnit.Level,
                        data.userUnit.Rank,
                        data.userUnit.Grade);
                    return (data.mstCharacter.Id, calculateStatus);
                })
                .ToList();

            // パーティステータス上昇矢印フラグの判定
            var isAdventBattle = specialRuleContentType == InGameContentType.AdventBattle;
            var hasAnyUnitWithStatusBoost = unitDataList.Any(data =>
            {
                var isSpecialRuleTarget = InGameSpecialRuleUnitStatusEvaluator.EvaluateTarget(
                    data.mstCharacter,
                    specialRuleUnitStatusModels);
                if (isSpecialRuleTarget) return true;

                if (isAdventBattle)
                {
                    var eventBonusPercentage = InGameEventBonusUnitEffectProvider.GetUnitEventBonusPercentageM(
                        data.mstCharacter.Id,
                        eventBonusGroupId);
                    var hasEventBonusOver100Percent = eventBonusPercentage > PercentageM.Hundred;
                    if (hasEventBonusOver100Percent) return true;
                }

                return false;
            });

            var totalPartyStatusUpperArrowFlag = hasAnyUnitWithStatusBoost
                ? TotalPartyStatusUpperArrowFlag.True
                : TotalPartyStatusUpperArrowFlag.False;

            var totalPartyStatus = TotalPartyStatusCalculator.CalculateTotalPartyStatus(
                unitStatusesList,
                GetEncyclopediaEffectModel(),
                eventBonusGroupId,
                specialRuleUnitStatusModels,
                specialRuleContentType);

            return new PartyFormationPartyModel(
                party.PartyNo,
                party.PartyName,
                totalPartyStatus,
                totalPartyStatusUpperArrowFlag,
                party.SlotCount,
                partyUnitNums,
                members);
        }

        PartyFormationPartyMemberModel TranslatePartyMemberModel(
            UserDataId userUnitId,
            IReadOnlyList<UserUnitModel> userUnitModels,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnitModels,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels,
            List<Rarity> partyRarities,
            List<MasterDataId> seriesIds,
            List<CharacterAttackRangeType> attackRangeTypes,
            List<CharacterUnitRoleType> unitRoleTypes,
            List<CharacterColor> characterColors,
            UnitListSortType sortType,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            if(userUnitId.IsEmpty()) return PartyFormationPartyMemberModel.Empty;

            var userUnit = userUnitModels.Find(model => model.UsrUnitId.Value == userUnitId.Value);
            var mstCharacter = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
            var bonusUnit = partyBonusUnitModels.Find(bonus => bonus.MstUnitId == mstCharacter.Id);
            var calculateStatus = UnitStatusCalculateHelper.Calculate(
                mstCharacter,
                userUnit.Level,
                userUnit.Rank,
                userUnit.Grade);
            var unitStatusWithSpecialRule = UnitStatusCalculateHelper.CalculateStatusWithSpecialRule(
                calculateStatus,
                mstCharacter.Id,
                specialRuleUnitStatusModels);

            bool isContainsRarity = partyRarities.IsEmpty() || partyRarities.Contains(mstCharacter.Rarity);
            bool isContainsSeries = seriesIds.IsEmpty() || seriesIds.Contains(mstCharacter.MstSeriesId);
            bool isContainsAttackRange = attackRangeTypes.IsEmpty() || attackRangeTypes.Contains(mstCharacter.AttackRangeType);
            bool isContainsUnitRole = unitRoleTypes.IsEmpty() || unitRoleTypes.Contains(mstCharacter.RoleType);
            bool isContainsColor = characterColors.IsEmpty() || characterColors.Contains(mstCharacter.Color);

            var inGameSpecialRuleUnitStatusTargetFlag = InGameSpecialRuleUnitStatusTargetFlag.False;
            if (!specialRuleUnitStatusModels.IsEmpty())
            {
                inGameSpecialRuleUnitStatusTargetFlag = InGameSpecialRuleUnitStatusEvaluator.EvaluateTarget(
                    userUnit,
                    specialRuleUnitStatusModels);
            }

            return new PartyFormationPartyMemberModel(
                userUnitId,
                UnitImageAssetPath.FromAssetKey(mstCharacter.AssetKey),
                mstCharacter.Color,
                mstCharacter.Rarity,
                userUnit.Level,
                mstCharacter.SummonCost,
                userUnit.Grade,
                unitStatusWithSpecialRule.HP,
                unitStatusWithSpecialRule.AttackPower,
                mstCharacter.RoleType,
                mstCharacter.AttackRangeType,
                mstCharacter.UnitMoveSpeed,
                sortType,
                bonusUnit?.BonusPercentage ?? EventBonusPercentage.Empty,
                CreatePartyFormationPartySpecialRuleItemModel(
                    userUnit,
                    mstInGameSpecialRuleModels,
                    isContainsRarity,
                    isContainsSeries,
                    isContainsAttackRange,
                    isContainsUnitRole,
                    isContainsColor),
                inGameSpecialRuleUnitStatusTargetFlag
            );
        }

        PartyFormationPartySpecialRuleItemModel CreatePartyFormationPartySpecialRuleItemModel(
            UserUnitModel userUnitModel,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels,
            bool isContainsRarity,
            bool isContainsSeries,
            bool isContainsAttackRange,
            bool isContainsUnitRole,
            bool isContainsColor)
        {
            InGameSpecialRuleAchievedFlag specialRuleFlag;
            // イベントが設定されていない場合は特別ルールを満たしているとする
            if (mstInGameSpecialRuleModels.Count <= 0)
            {
                specialRuleFlag = InGameSpecialRuleAchievedFlag.True;
            }
            else
            {
                specialRuleFlag = new InGameSpecialRuleAchievedFlag(
                    isContainsRarity &&
                    isContainsSeries &&
                    isContainsAttackRange &&
                    isContainsUnitRole &&
                    isContainsColor);
            }
            return new PartyFormationPartySpecialRuleItemModel(userUnitModel.UsrUnitId, specialRuleFlag);
        }

        List<MstInGameSpecialRuleModel> GetMstStageEventRuleModels(
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels,
            RuleType ruleType)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == ruleType)
                .ToList();
        }

        List<MasterDataId> GetAndTranslateSeriesIds(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Select(model => model.RuleValue.ToSeriesId())
                .ToList();
        }

        List<Rarity> GetAndTranslateRarities(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Select(model => model.RuleValue.ToRarity())
                .ToList();
        }

        List<CharacterUnitRoleType> GetAndTranslateUnitRoleTypes(
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Select(model => model.RuleValue.ToUnitRoleType())
                .ToList();
        }

        List<CharacterColor> GetAndTranslateUnitColor(
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Select(model => model.RuleValue.ToCharacterColor())
                .ToList();
        }

        List<CharacterAttackRangeType> GetAndTranslateAttackRangeTypes(
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Select(model => model.RuleValue.ToAttackRangeType())
                .ToList();
        }

        SpecialRulePartyUnitNum GetAndTranslatePartyMemberNums(
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            if(mstInGameSpecialRuleModels.IsEmpty())
            {
                return SpecialRulePartyUnitNum.Empty;
            }
            var partyUnitNum = mstInGameSpecialRuleModels
                .Select(model => model.RuleValue.ToInt())
                .Min();

            return new SpecialRulePartyUnitNum(partyUnitNum);
        }

        InGameUnitEncyclopediaEffectModel GetEncyclopediaEffectModel()
        {
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            var grade = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaGrade(userUnits);
            var mstRewards = MstUnitEncyclopediaRewardDataRepository.GetUnitEncyclopediaRewards();
            var mstEffects = MstUnitEncyclopediaEffectDataRepository.GetUnitEncyclopediaEffects();

            var hp = UnitEncyclopediaEffectCalculator
                .CalculateUnitEncyclopediaUnitEffectValue(mstRewards, mstEffects, grade, UnitEncyclopediaEffectType.Hp);
            var attackPower = UnitEncyclopediaEffectCalculator
                .CalculateUnitEncyclopediaUnitEffectValue(mstRewards, mstEffects, grade, UnitEncyclopediaEffectType.AttackPower);
            var heal = UnitEncyclopediaEffectCalculator
                .CalculateUnitEncyclopediaUnitEffectValue(mstRewards, mstEffects, grade, UnitEncyclopediaEffectType.Heal);

            return new InGameUnitEncyclopediaEffectModel(
                hp.ToPercentageM() + PercentageM.Hundred,
                attackPower.ToPercentageM() + PercentageM.Hundred,
                heal.ToPercentageM() + PercentageM.Hundred);
        }
    }
}
