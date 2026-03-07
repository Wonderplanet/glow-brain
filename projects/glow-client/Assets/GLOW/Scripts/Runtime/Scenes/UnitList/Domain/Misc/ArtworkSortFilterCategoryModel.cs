using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Models;

namespace GLOW.Scenes.UnitList.Domain.Misc
{
    public record ArtworkSortFilterCategoryModel(
        FilterSeriesModel FilterSeriesModel,
        FilterArtworkEffectModel FilterArtworkEffectModel,
        ArtworkListSortType SortType,
        ArtworkListSortOrder SortOrder)
    {
        public static ArtworkSortFilterCategoryModel Default { get; } = new ArtworkSortFilterCategoryModel(
            FilterSeriesModel.Default,
            FilterArtworkEffectModel.Default,
            ArtworkListSortType.Rarity,
            ArtworkListSortOrder.Descending);

        public bool IsAnyFilter()
        {
            return FilterSeriesModel.IsAnyFilter || FilterArtworkEffectModel.IsAnyFilter;
        }

        public bool EqualsSeries(MasterDataId seriesId)
        {
            if (!FilterSeriesModel.IsAnyFilter)
            {
                return true;
            }

            return FilterSeriesModel.SeriesIds.Contains(seriesId);
        }

        public bool EqualsArtworkEffectType(ArtworkEffectType artworkEffectType)
        {
            if (!FilterArtworkEffectModel.IsAnyFilter)
            {
                return true;
            }

            return FilterArtworkEffectModel.ArtworkEffectTypes.Contains(artworkEffectType);
        }
    }
}
