using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShopGacha.Presentation.ViewModels
{
    public record PackShopGachaCellViewModel(
        MasterDataId GachaId,
        PackShopGachaBannerAssetPath GachaBannerAssetPath);
}
