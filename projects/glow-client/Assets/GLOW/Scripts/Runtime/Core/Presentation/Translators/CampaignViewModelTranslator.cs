using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Core.Presentation.Translators
{
    public static class CampaignViewModelTranslator
    {
        public static CampaignViewModel ToCampaignViewModel(CampaignModel campaignModel)
        {
            if (campaignModel.IsEmpty())
            {
                return CampaignViewModel.Empty;
            }

            return new CampaignViewModel(
                campaignModel.CampaignType,
                campaignModel.Title,
                campaignModel.Description,
                campaignModel.RemainingTimeSpan,
                campaignModel.CampaignTargetType);
        }
    }
}
