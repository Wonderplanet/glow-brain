using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Domain.UseCases
{
    /// <summary> ソート・フィルターのRepositoryへの更新処理を行う </summary>
    public class UpdateSortFilterCacheUseCase
    {
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }

        public void UpdateModel(
            UnitSortFilterCacheType cacheType,
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
            UnitListSortOrder sortOrder)
        {
            var updateModel = new UnitSortFilterCategoryModel(
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
            UpdateModel(cacheType, updateModel);
        }

        public void UpdateModel(UnitSortFilterCacheType cacheType, UnitSortFilterCategoryModel updateModel)
        {
            UnitSortFilterCacheRepository.UpdateModel(updateModel, cacheType);
        }
    }
}
