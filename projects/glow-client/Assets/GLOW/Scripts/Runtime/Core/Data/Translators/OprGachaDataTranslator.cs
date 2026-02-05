using System;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public static class OprGachaDataTranslator
    {
        public static OprGachaModel Translate(OprGachaData oprGachaData, OprGachaI18nData i18nData)
        {

            var startAt = oprGachaData.StartAt >= UnlimitedCalculableDateTimeOffset.UnlimitedStartAt
                ? oprGachaData.StartAt
                : UnlimitedCalculableDateTimeOffset.UnlimitedStartAt;

            var endAt =
                oprGachaData.EndAt != DateTimeOffset.MinValue &&
                oprGachaData.EndAt <= UnlimitedCalculableDateTimeOffset.UnlimitedEndAt
                ? oprGachaData.EndAt
                : UnlimitedCalculableDateTimeOffset.UnlimitedEndAt;

            return new OprGachaModel(
                Id: new MasterDataId(oprGachaData.Id),
                GachaType: oprGachaData.GachaType,
                DrawCountThresholdGroupId: new DrawCountThresholdGroupId(oprGachaData.UpperGroup),
                EnableAdPlay: new EnableAdPlay(oprGachaData.EnableAdPlay),
                AdIntervalTimeMinutes: oprGachaData.AdPlayIntervalTime != null 
                    ? new IntervalTimeMinutes(oprGachaData.AdPlayIntervalTime.Value) : IntervalTimeMinutes.Empty,
                MultiDrawCount: new GachaDrawCount(oprGachaData.MultiDrawCount),
                DailyPlayLimitCount: oprGachaData.DailyPlayLimitCount != null 
                    ? new GachaDrawLimitCount(oprGachaData.DailyPlayLimitCount.Value) : GachaDrawLimitCount.Unlimited,
                TotalPlayLimitCount: oprGachaData.TotalPlayLimitCount != null 
                    ? new GachaDrawLimitCount(oprGachaData.TotalPlayLimitCount.Value) : GachaDrawLimitCount.Unlimited,
                DailyAdPlayLimitCount: oprGachaData.DailyAdLimitCount != null 
                    ? new GachaDrawLimitCount(oprGachaData.DailyAdLimitCount.Value) : GachaDrawLimitCount.Unlimited,
                TotalAdPlayLimitCount: oprGachaData.TotalAdLimitCount != null 
                    ? new GachaDrawLimitCount(oprGachaData.TotalAdLimitCount.Value) : GachaDrawLimitCount.Unlimited,
                AnnouncementId: !string.IsNullOrEmpty(oprGachaData.DisplayInformationId) 
                    ? new AnnouncementId(oprGachaData.DisplayInformationId) : AnnouncementId.Empty,
                GachaCautionId: !string.IsNullOrEmpty(oprGachaData.DisplayGachaCautionId) 
                    ? new GachaCautionId(oprGachaData.DisplayGachaCautionId) : GachaCautionId.Empty,
                StartAt: startAt,
                EndAt: endAt,
                GachaName: new GachaName(i18nData.Name),
                Description: new GachaDescription(i18nData.Description),
                GachaBannerAssetKey: new GachaBannerAssetKey(i18nData.BannerUrl),
                GachaPriority: new GachaPriority(oprGachaData.GachaPriority),
                RarityThresholdText: new GachaThresholdText(i18nData.MaxRarityUpperDescription),
                PickupThresholdText: new GachaThresholdText(i18nData.PickupUpperDescription),
                UnlockConditionType: oprGachaData.UnlockConditionType,
                UnlockDurationHours: oprGachaData.UnlockDurationHours != null && oprGachaData.UnlockDurationHours.Value != 0
                    ? new GachaUnlockDurationHours(oprGachaData.UnlockDurationHours.Value)
                    : GachaUnlockDurationHours.Empty,
                GachaFixedPrizeDescription: !string.IsNullOrEmpty(i18nData.FixedPrizeDescription)
                    ? new GachaFixedPrizeDescription(i18nData.FixedPrizeDescription) 
                    : GachaFixedPrizeDescription.Empty,
                GachaLogoAssetKey: !string.IsNullOrEmpty(i18nData.LogoAssetKey)
                    ? new GachaLogoAssetKey(i18nData.LogoAssetKey)
                    : GachaLogoAssetKey.Empty,
                AppearanceCondition: oprGachaData.AppearanceCondition
            );
        }
    }
}
