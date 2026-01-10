using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShopGacha.Domain.Models
{
    public record PackShopGachaBannerModel(MasterDataId GachaId, GachaBannerAssetPath GachaBannerAssetPath)
    {
        public static PackShopGachaBannerModel Empty { get; } =
            new PackShopGachaBannerModel(MasterDataId.Empty, GachaBannerAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}