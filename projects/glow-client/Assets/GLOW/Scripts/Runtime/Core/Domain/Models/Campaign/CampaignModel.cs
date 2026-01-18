using System;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.Campaign;

namespace GLOW.Core.Domain.Models.Campaign
{
    public record CampaignModel(
        CampaignType CampaignType,
        CampaignTitle Title,
        CampaignDescription Description,
        RemainingTimeSpan RemainingTimeSpan,
        CampaignEffectValue EffectValue,
        CampaignTargetType CampaignTargetType)
    {
        public static CampaignModel Empty { get; } = new(
            CampaignType.Stamina,
            CampaignTitle.Empty,
            CampaignDescription.Empty,
            RemainingTimeSpan.Empty,
            CampaignEffectValue.Empty,
            CampaignTargetType.NormalQuest);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsStaminaCampaign()
        {
            return CampaignType == CampaignType.Stamina;
        }

        public bool IsChallengeCountCampaign()
        {
            return CampaignType == CampaignType.ChallengeCount;
        }
    }
}
