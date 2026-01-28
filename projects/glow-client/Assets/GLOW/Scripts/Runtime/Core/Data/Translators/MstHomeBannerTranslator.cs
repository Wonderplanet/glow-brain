using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstHomeBannerTranslator
    {
        public static MstHomeBannerModel Translate(MstHomeBannerData data)
        {
            var destinationPath = string.IsNullOrEmpty(data.DestinationPath)
                ? HomeBannerDestinationPath.Empty
                : new HomeBannerDestinationPath(data.DestinationPath);
            return new MstHomeBannerModel(
                new MasterDataId(data.Id),
                data.Destination,
                destinationPath,
                new HomeBannerAssetKey(data.AssetKey),
                data.StartAt,
                data.EndAt,
                new SortOrder(data.SortOrder)
            );
        }
    }
}
