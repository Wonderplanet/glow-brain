using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record GachaBannerModel(
        MasterDataId GachaId,
        GachaType GachaType,
        GachaBannerAssetPath GachaBannerAssetPath,
        NotificationBadge NotificationBadge,
        GachaRemainingTimeText GachaRemainingTimeText,
        GachaDescription GachaDescription,
        GachaPriority GachaPriority,
        GachaThresholdText GachaThresholdText)
    {
        public static GachaBannerModel Empty { get; } = new GachaBannerModel(
            MasterDataId.Empty,
            GachaType.Normal,
            GachaBannerAssetPath.Empty,
            NotificationBadge.False,
            GachaRemainingTimeText.Empty,
            GachaDescription.Empty,
            GachaPriority.Empty,
            GachaThresholdText.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
