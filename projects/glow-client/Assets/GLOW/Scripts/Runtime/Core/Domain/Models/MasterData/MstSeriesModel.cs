using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstSeriesModel(
        MasterDataId Id,
        SeriesName Name,
        SeriesAssetKey SeriesAssetKey,
        SeriesBannerAssetKey SeriesBannerAssetKey,
        SeriesPrefixWord PrefixWord,
        JumpPlusUrl JumpPlusUrl
        )
    {
        public static MstSeriesModel Empty { get; } = new MstSeriesModel(
            MasterDataId.Empty,
            SeriesName.Empty,
            SeriesAssetKey.Empty,
            SeriesBannerAssetKey.Empty,
            SeriesPrefixWord.Empty,
            JumpPlusUrl.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
