using System.Collections.Generic;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitList.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IUnitSortFilterCacheRepository
    {
        void Clear();

        void UpdateSortOrder(UnitListSortOrder sortOrder, UnitSortFilterCacheType type);
        void UpdateBonusFilter(UnitSortFilterCacheType type, FilterBonusModel bonusModel);
        void UpdateFormationFilter(UnitSortFilterCacheType type, FilterFormationModel formationModel);
        void UpdateModel(UnitSortFilterCategoryModel updateModel, UnitSortFilterCacheType type);

        UnitSortFilterCategoryModel GetModel(UnitSortFilterCacheType type);
        FilterRoleModel GetFilterRole(UnitSortFilterCacheType type);
        FilterAttackRangeModel GetFilterAttackRange(UnitSortFilterCacheType type);
        FilterRarityModel GetFilterRarity(UnitSortFilterCacheType type);
        FilterAbilityModel GetFilterAbility(UnitSortFilterCacheType type);
        FilterSpecialAttackModel GetFilterSpecialAttack(UnitSortFilterCacheType type);
        FilterSeriesModel GetFilterSeriesModel(UnitSortFilterCacheType type);

        UnitListSortType GetSortType(UnitSortFilterCacheType type);
        UnitListSortOrder GetSortOrder(UnitSortFilterCacheType type);
    }
}
