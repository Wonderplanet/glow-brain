using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitList.Domain.Models;

namespace GLOW.Core.Data.Repositories
{
    public class UnitSortFilterCacheRepository : IUnitSortFilterCacheRepository
    {
        Dictionary<UnitSortFilterCacheType, UnitSortFilterCategoryModel> _sortFilterCategoryModel =
            new ()
            {
                { UnitSortFilterCacheType.UnitList,  UnitSortFilterCategoryModel.Default},
                { UnitSortFilterCacheType.PartyFormation,  UnitSortFilterCategoryModel.Default},
                { UnitSortFilterCacheType.PartyFormationWithEventBonus,  UnitSortFilterCategoryModel.Default}
            };

        void IUnitSortFilterCacheRepository.Clear()
        {
            _sortFilterCategoryModel = new()
            {
                { UnitSortFilterCacheType.UnitList,  UnitSortFilterCategoryModel.Default},
                { UnitSortFilterCacheType.PartyFormation,  UnitSortFilterCategoryModel.Default},
                { UnitSortFilterCacheType.PartyFormationWithEventBonus,  UnitSortFilterCategoryModel.Default}
            };
        }

        void IUnitSortFilterCacheRepository.UpdateSortOrder(UnitListSortOrder sortOrder, UnitSortFilterCacheType type)
        {
            _sortFilterCategoryModel[type] = _sortFilterCategoryModel[type] with
            {
                SortOrder = sortOrder,
            };
        }

        void IUnitSortFilterCacheRepository.UpdateBonusFilter(UnitSortFilterCacheType type, FilterBonusModel bonusModel)
        {
            _sortFilterCategoryModel[type] = _sortFilterCategoryModel[type] with
            {
                FilterBonusModel = bonusModel
            };
        }

        void IUnitSortFilterCacheRepository.UpdateFormationFilter(UnitSortFilterCacheType type, FilterFormationModel formationModel)
        {
            _sortFilterCategoryModel[type] = _sortFilterCategoryModel[type] with
            {
                FilterFormationModel = formationModel
            };
        }

        void IUnitSortFilterCacheRepository.UpdateModel(UnitSortFilterCategoryModel updateModel, UnitSortFilterCacheType type)
        {
            _sortFilterCategoryModel[type] = updateModel;
        }

        UnitSortFilterCategoryModel IUnitSortFilterCacheRepository.GetModel(UnitSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type];
        }

        FilterRoleModel IUnitSortFilterCacheRepository.GetFilterRole(UnitSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type].FilterRoleModel;
        }

        FilterAttackRangeModel IUnitSortFilterCacheRepository.GetFilterAttackRange(UnitSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type].FilterAttackRangeModel;
        }

        FilterRarityModel IUnitSortFilterCacheRepository.GetFilterRarity(UnitSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type].FilterRarityModel;
        }

        FilterAbilityModel IUnitSortFilterCacheRepository.GetFilterAbility(UnitSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type].FilterAbilityModel;
        }

        FilterSpecialAttackModel IUnitSortFilterCacheRepository.GetFilterSpecialAttack(UnitSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type].FilterSpecialAttackModel;
        }

        FilterSeriesModel IUnitSortFilterCacheRepository.GetFilterSeriesModel(UnitSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type].FilterSeriesModel;
        }

        UnitListSortType IUnitSortFilterCacheRepository.GetSortType(UnitSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type].SortType;
        }

        UnitListSortOrder IUnitSortFilterCacheRepository.GetSortOrder(UnitSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type].SortOrder;
        }
    }
}
