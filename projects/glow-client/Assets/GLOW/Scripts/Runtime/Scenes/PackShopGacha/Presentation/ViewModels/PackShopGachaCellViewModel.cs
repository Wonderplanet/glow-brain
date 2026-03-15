using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShopGacha.Presentation.ViewModels
{
    public record PackShopGachaCellViewModel(
        MasterDataId GachaId,
        GachaType GachaType,
        PackShopGachaBannerAssetPath GachaBannerAssetPath);
}
