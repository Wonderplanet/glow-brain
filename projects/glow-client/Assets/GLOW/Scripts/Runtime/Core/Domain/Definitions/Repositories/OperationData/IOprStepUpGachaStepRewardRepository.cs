using System.Collections.Generic;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IOprStepUpGachaStepRewardRepository
    {
        IReadOnlyList<OprStepUpGachaStepRewardModel> GetOprStepUpGachaStepRewardModels(MasterDataId oprGachaId);
    }
}

