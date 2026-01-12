using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Factories
{
    public interface ICampaignModelFactory
    {
        CampaignModel CreateCampaignModel(
            MasterDataId targetId,
            CampaignTargetType campaignTargetType,
            CampaignTargetIdType campaignTargetIdType,
            Difficulty difficulty,
            CampaignType campaignType);

        List<CampaignModel> CreateCampaignModels(
            MasterDataId targetId,
            CampaignTargetType campaignTargetType,
            CampaignTargetIdType campaignTargetIdType,
            Difficulty difficulty);

        List<CampaignModel> CreateCampaignModels(CampaignTargetType campaignTargetType, CampaignTargetIdType campaignTargetIdType);
    }
}
