using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record FestivalGachaBannerViewModel(
        MasterDataId MstGachaId,
        GachaType GachaType,
        FestivalGachaBannerAssetPath FestivalGachaBannerAssetPath,
        NotificationBadge NotificationBadge,
        GachaRemainingTimeText GachaRemainingTimeText,
        GachaDescription GachaDescription,
        GachaThresholdText GachaThresholdText)
    {
        public static FestivalGachaBannerViewModel Empty { get;} = 
            new FestivalGachaBannerViewModel(
                MasterDataId.Empty,
                GachaType.Normal,
                FestivalGachaBannerAssetPath.Empty,
                new NotificationBadge(false),
                GachaRemainingTimeText.Empty,
                GachaDescription.Empty,
                GachaThresholdText.Empty);

        public bool IsEmpty()
        {
            return MstGachaId.IsEmpty();
        }
    }
}

