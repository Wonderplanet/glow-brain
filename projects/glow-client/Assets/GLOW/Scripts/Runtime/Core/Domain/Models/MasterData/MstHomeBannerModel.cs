using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstHomeBannerModel(
        MasterDataId Id,
        HomeBannerDestinationType DestinationType,
        HomeBannerDestinationPath DestinationPath,
        HomeBannerAssetKey BannerAssetKey,
        DateTimeOffset StartAt,
        DateTimeOffset EndAt,
        SortOrder SortOrder)
    {
        public static MstHomeBannerModel Empty { get; } = new(
            MasterDataId.Empty,
            HomeBannerDestinationType.None,
            HomeBannerDestinationPath.Empty,
            HomeBannerAssetKey.Empty,
            DateTimeOffset.MinValue,
            DateTimeOffset.MinValue,
            SortOrder.Empty);
    };
}
