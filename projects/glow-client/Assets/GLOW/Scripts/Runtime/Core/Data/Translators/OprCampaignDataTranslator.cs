using System;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Campaign;

namespace GLOW.Core.Data.Translators
{
    public static class OprCampaignDataTranslator
    {
        public static OprCampaignModel Translate(OprCampaignData oprCampaignData, OprCampaignI18nData oprCampaignI18nData)
        {
            var startAt = oprCampaignData.StartAt >= UnlimitedCalculableDateTimeOffset.UnlimitedStartAt
                ? oprCampaignData.StartAt
                : UnlimitedCalculableDateTimeOffset.UnlimitedStartAt;

            var endAt =
                oprCampaignData.EndAt != DateTimeOffset.MinValue &&
                oprCampaignData.EndAt <= UnlimitedCalculableDateTimeOffset.UnlimitedEndAt
                ? oprCampaignData.EndAt
                : UnlimitedCalculableDateTimeOffset.UnlimitedEndAt;

            return new OprCampaignModel(
                string.IsNullOrEmpty(oprCampaignData.Id) ?
                    MasterDataId.Empty :
                    new MasterDataId(oprCampaignData.Id),
                oprCampaignData.CampaignType,
                oprCampaignData.TargetType,
                oprCampaignData.Difficulty,
                oprCampaignData.TargetIdType,
                string.IsNullOrEmpty(oprCampaignData.TargetId) ?
                    MasterDataId.Empty :
                    new MasterDataId(oprCampaignData.TargetId),
                new CampaignEffectValue(oprCampaignData.EffectValue),
                string.IsNullOrEmpty(oprCampaignI18nData.Description) ?
                    CampaignDescription.Empty :
                    new CampaignDescription(oprCampaignI18nData.Description),
                new CampaignStartAt(startAt),
                new CampaignEndAt(endAt));
        }
    }
}
