using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.OprData
{
    public record OprGachaModel(
        MasterDataId Id,
        GachaType GachaType,
        DrawCountThresholdGroupId DrawCountThresholdGroupId,
        EnableAdPlay EnableAdPlay,
        IntervalTimeMinutes AdIntervalTimeMinutes,
        GachaDrawCount MultiDrawCount,
        GachaDrawLimitCount DailyPlayLimitCount,
        GachaDrawLimitCount TotalPlayLimitCount,
        GachaDrawLimitCount DailyAdPlayLimitCount,
        GachaDrawLimitCount TotalAdPlayLimitCount,
        AnnouncementId AnnouncementId,
        GachaCautionId GachaCautionId,
        DateTimeOffset StartAt,
        DateTimeOffset EndAt,
        GachaName GachaName,
        GachaDescription Description,
        GachaBannerAssetKey GachaBannerAssetKey,
        GachaPriority GachaPriority,
        GachaThresholdText RarityThresholdText,
        GachaThresholdText PickupThresholdText,
        GachaUnlockConditionType UnlockConditionType,
        GachaUnlockDurationHours UnlockDurationHours,
        GachaFixedPrizeDescription GachaFixedPrizeDescription,
        GachaLogoAssetKey GachaLogoAssetKey,
        AppearanceCondition AppearanceCondition
    )
    {
        public static OprGachaModel Empty { get; } = new OprGachaModel(
                MasterDataId.Empty,
                GachaType.Normal,
                DrawCountThresholdGroupId.Empty,
                EnableAdPlay.False,
                IntervalTimeMinutes.Empty,
                GachaDrawCount.Empty,
                GachaDrawLimitCount.Zero,
                GachaDrawLimitCount.Zero,
                GachaDrawLimitCount.Zero,
                GachaDrawLimitCount.Zero,
                AnnouncementId.Empty,
                GachaCautionId.Empty,
                DateTimeOffset.MaxValue,//弾かれるようにStartAtにMax入れる
                DateTimeOffset.MinValue, //弾かれるようにEndAtにMin入れる
                GachaName.Empty,
                GachaDescription.Empty,
                GachaBannerAssetKey.Empty,
                GachaPriority.Empty,
                GachaThresholdText.Empty,
                GachaThresholdText.Empty,
                GachaUnlockConditionType.None,
                GachaUnlockDurationHours.Empty,
                GachaFixedPrizeDescription.Empty,
                GachaLogoAssetKey.Empty,
                AppearanceCondition.Always
                );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
