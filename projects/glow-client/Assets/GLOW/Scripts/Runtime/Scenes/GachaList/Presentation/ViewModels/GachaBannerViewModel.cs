using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record GachaBannerViewModel(
        MasterDataId GachaId,
        GachaType GachaType,
        GachaBannerAssetPath GachaBannerAssetPath,
        NotificationBadge NotificationBadge,
        GachaRemainingTimeText GachaRemainingTimeText,
        GachaDescription GachaDescription,
        GachaThresholdText GachaThresholdText
        );
}
