using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShopGacha.Domain.Models
{
    public record PackShopGachaBannerModel(
        MasterDataId GachaId,
        PackShopGachaBannerAssetPath PackShopGachaBannerAssetPath)
    {
        public static PackShopGachaBannerModel Empty { get; } =
            new PackShopGachaBannerModel(MasterDataId.Empty, PackShopGachaBannerAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
