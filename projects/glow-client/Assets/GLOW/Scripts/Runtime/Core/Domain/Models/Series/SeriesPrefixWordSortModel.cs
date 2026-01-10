using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Series
{
    public record SeriesPrefixWordSortModel(
        MasterDataId Id,
        SeriesName Name,
        SeriesAssetKey SeriesAssetKey,
        SeriesBannerAssetKey SeriesBannerAssetKey,
        SeriesPrefixWord PrefixWord,
        JumpPlusUrl JumpPlusUrl,
        PrefixWordSortOrder PrefixWordSortOrder)
    {
        public static SeriesPrefixWordSortModel Empty { get; } = new SeriesPrefixWordSortModel(
            MasterDataId.Empty,
            SeriesName.Empty,
            SeriesAssetKey.Empty,
            SeriesBannerAssetKey.Empty,
            SeriesPrefixWord.Empty,
            JumpPlusUrl.Empty,
            PrefixWordSortOrder.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
