using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record FestivalGachaBannerModel(
        MasterDataId GachaId,
        GachaType GachaType,
        FestivalGachaBannerAssetPath FestivalGachaBannerAssetPath,
        NotificationBadge NotificationBadge,
        GachaRemainingTimeText GachaRemainingTimeText,
        GachaDescription GachaDescription,
        GachaThresholdText GachaThresholdText)
    {
        public static FestivalGachaBannerModel Empty
        {
            get
            {
                return new FestivalGachaBannerModel(
                    MasterDataId.Empty,
                    GachaType.Normal,
                    FestivalGachaBannerAssetPath.Empty,
                    new NotificationBadge(false),
                    GachaRemainingTimeText.Empty,
                    GachaDescription.Empty,
                    GachaThresholdText.Empty);
            }
        }

        public bool IsEmpty()
        {
            return GachaId.IsEmpty();
        }
    }
}

