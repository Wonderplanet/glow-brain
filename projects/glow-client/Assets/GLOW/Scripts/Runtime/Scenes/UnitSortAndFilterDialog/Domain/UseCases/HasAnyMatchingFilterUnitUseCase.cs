using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Domain.UseCases
{
    public class HasAnyMatchingFilterUnitUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IUnitListFilterAndSort UnitListFilterAndSort { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }

        // 設定したフィルターに該当するユニットがいればtrueを返す
        public bool HasAnyMatchingFilterUnit(
            IReadOnlyList<Rarity> rarities,
            IReadOnlyList<CharacterColor> colors,
            IReadOnlyList<CharacterUnitRoleType> roleTypes,
            IReadOnlyList<CharacterAttackRangeType> rangeTypes,
            IReadOnlyList<MasterDataId> seriesIds,
            IReadOnlyList<FilterSpecialAttack> specialAttacks,
            IReadOnlyList<UnitAbilityType> abilityTypes,
            FilterBonusFlag enableBonus,
            FilterBonusFlag bonusFlag,
            FilterFormationFlag enableFormation,
            FilterAchievedSpecialRuleFlag filterAchievedFlag,
            FilterNotAchieveSpecialRuleFlag filterNotAchievedFlag,
            UnitListSortType sortType,
            UnitListSortOrder sortOrder,
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType)
        {
            var filterCategoryModel = new UnitSortFilterCategoryModel(
                new FilterColorModel(colors),
                new FilterRoleModel(roleTypes),
                new FilterAttackRangeModel(rangeTypes),
                new FilterRarityModel(rarities),
                new FilterSpecialAttackModel(specialAttacks),
                new FilterAbilityModel(abilityTypes),
                new FilterSeriesModel(seriesIds),
                new FilterBonusModel(enableBonus, bonusFlag),
                new FilterFormationModel(enableFormation, filterAchievedFlag, filterNotAchievedFlag),
                sortType,
                sortOrder);

            if (!filterCategoryModel.IsAnyFilter())
            {
                return true;
            }

            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            var mstUnits = userUnits
                .Select(unit => MstCharacterDataRepository.GetCharacter(unit.MstUnitId))
                .ToList();

            var partyBonusUnits = PartyCacheRepository.GetBonusUnits();

            var mstSeriesModels = MstSeriesDataRepository.GetMstSeriesModels();

            var achievedSpecialRuleUnitIds =
                GetAchievedSpecialRuleUnitIds(userUnits, specialRuleTargetMstId, specialRuleContentType);
            var notAchieveSpecialRuleUnitIds = userUnits
                .Select(unit => unit.UsrUnitId)
                .Except(achievedSpecialRuleUnitIds)
                .ToList();

            var mstUnitLevelUpModels = MstUnitLevelUpRepository.GetUnitLevelUpList();

            var hasAnyMatching = UnitListFilterAndSort.HasAnyMatchingFilterUnit(
                userUnits,
                mstUnits,
                partyBonusUnits,
                filterCategoryModel,
                mstSeriesModels,
                mstUnitLevelUpModels,
                achievedSpecialRuleUnitIds,
                notAchieveSpecialRuleUnitIds);

            return hasAnyMatching;
        }

        IReadOnlyList<UserDataId> GetAchievedSpecialRuleUnitIds(
            IReadOnlyList<UserUnitModel> userUnitModels,
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType)
        {
            if (specialRuleTargetMstId.IsEmpty())
            {
                return new List<UserDataId>();
            }
            var mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                specialRuleTargetMstId,
                specialRuleContentType);
            return userUnitModels
                .Where(unit =>
                {
                    var mstCharacter = MstCharacterDataRepository.GetCharacter(unit.MstUnitId);
                    return InGameSpecialRuleAchievingEvaluator.IsAchievedSpecialRule(
                        mstCharacter,
                        mstInGameSpecialRuleModels);
                })
                .Select(unit => unit.UsrUnitId)
                .ToList();
        }
    }
}
