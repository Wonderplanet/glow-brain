using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.Campaign;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.OprData
{
    public record OprCampaignModel(
        MasterDataId CampaignId,
        CampaignType CampaignType,
        CampaignTargetType CampaignTargetType,
        Difficulty Difficulty,
        CampaignTargetIdType CampaignTargetIdType,
        MasterDataId TargetId,
        CampaignEffectValue EffectValue,
        CampaignDescription Description,
        CampaignStartAt StartAt,
        CampaignEndAt EndAt)
    {
        public static OprCampaignModel Empty { get; } = new OprCampaignModel(
            MasterDataId.Empty,
            CampaignType.Stamina,
            CampaignTargetType.NormalQuest,
            Difficulty.Normal,
            CampaignTargetIdType.Quest,
            MasterDataId.Empty,
            CampaignEffectValue.Empty,
            CampaignDescription.Empty,
            CampaignStartAt.Empty,
            CampaignEndAt.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
