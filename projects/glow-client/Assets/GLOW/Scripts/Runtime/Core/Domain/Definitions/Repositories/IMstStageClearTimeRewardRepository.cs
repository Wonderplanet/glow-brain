using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstStageClearTimeRewardRepository
    {
        IReadOnlyList<MstStageClearTimeRewardModel> GetClearTimeRewards(MasterDataId mstStageId);
    }
}
