using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShopGacha.Domain.Models
{
    public record PackShopGachaBannerModel(
        MasterDataId GachaId,
        GachaType GachaType,
        PackShopGachaBannerAssetPath PackShopGachaBannerAssetPath)
    {
        public static PackShopGachaBannerModel Empty { get; } =
            new PackShopGachaBannerModel(MasterDataId.Empty, GachaType.Normal, PackShopGachaBannerAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
