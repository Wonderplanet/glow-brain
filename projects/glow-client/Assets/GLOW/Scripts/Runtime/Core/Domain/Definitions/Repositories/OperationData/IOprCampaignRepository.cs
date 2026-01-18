using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IOprCampaignRepository
    {
        IReadOnlyList<OprCampaignModel> GetOprCampaignModelsByDataTime(DateTimeOffset dateTime);
        OprCampaignModel GetOprCampaignModelFirstOrDefaultById(MasterDataId campaignId);
        IReadOnlyList<OprCampaignModel> GetOprCampaignModelByIds(IReadOnlyList<MasterDataId> campaignIds);
    }
}
