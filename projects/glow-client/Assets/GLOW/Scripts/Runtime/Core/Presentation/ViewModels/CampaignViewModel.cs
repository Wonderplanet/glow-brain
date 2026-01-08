using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Campaign;

namespace GLOW.Core.Presentation.ViewModels
{
    public record CampaignViewModel(
        CampaignType CampaignType,
        CampaignTitle Title,
        CampaignDescription Description,
        RemainingTimeSpan RemainingTimeSpan,
        CampaignTargetType CampaignTargetType)
    {
        public static CampaignViewModel Empty { get; } = new(
            CampaignType.Stamina,
            CampaignTitle.Empty,
            CampaignDescription.Empty,
            RemainingTimeSpan.Empty,
            CampaignTargetType.NormalQuest);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
