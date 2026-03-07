using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Core.Domain.Repositories
{
    public interface IArtworkSortFilterCacheRepository
    {
        void Clear();

        void UpdateSortOrder(ArtworkListSortOrder sortOrder, ArtworkSortFilterCacheType type);
        void UpdateModel(ArtworkSortFilterCategoryModel updateModel, ArtworkSortFilterCacheType type);

        ArtworkSortFilterCategoryModel GetModel(ArtworkSortFilterCacheType type);

        ArtworkListSortType GetSortType(ArtworkSortFilterCacheType type);
    }
}
