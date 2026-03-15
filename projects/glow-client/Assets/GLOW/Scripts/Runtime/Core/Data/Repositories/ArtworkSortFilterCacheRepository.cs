using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Core.Data.Repositories
{
    public class ArtworkSortFilterCacheRepository : IArtworkSortFilterCacheRepository
    {
        Dictionary<ArtworkSortFilterCacheType, ArtworkSortFilterCategoryModel> _sortFilterCategoryModel =
            new ()
            {
                { ArtworkSortFilterCacheType.ArtworkList,  ArtworkSortFilterCategoryModel.Default },
                { ArtworkSortFilterCacheType.PartyFormation,  ArtworkSortFilterCategoryModel.Default },
            };

        void IArtworkSortFilterCacheRepository.Clear()
        {
            _sortFilterCategoryModel = new Dictionary<ArtworkSortFilterCacheType, ArtworkSortFilterCategoryModel>()
            {
                { ArtworkSortFilterCacheType.ArtworkList, ArtworkSortFilterCategoryModel.Default },
                { ArtworkSortFilterCacheType.PartyFormation, ArtworkSortFilterCategoryModel.Default },
            };
        }

        void IArtworkSortFilterCacheRepository.UpdateSortOrder(ArtworkListSortOrder sortOrder, ArtworkSortFilterCacheType type)
        {
            _sortFilterCategoryModel[type] = _sortFilterCategoryModel[type] with
            {
                SortOrder = sortOrder,
            };
        }

        void IArtworkSortFilterCacheRepository.UpdateModel(ArtworkSortFilterCategoryModel updateModel, ArtworkSortFilterCacheType type)
        {
            _sortFilterCategoryModel[type] = updateModel;
        }

        ArtworkSortFilterCategoryModel IArtworkSortFilterCacheRepository.GetModel(ArtworkSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type];
        }

        ArtworkListSortType IArtworkSortFilterCacheRepository.GetSortType(ArtworkSortFilterCacheType type)
        {
            return _sortFilterCategoryModel[type].SortType;
        }
    }
}
