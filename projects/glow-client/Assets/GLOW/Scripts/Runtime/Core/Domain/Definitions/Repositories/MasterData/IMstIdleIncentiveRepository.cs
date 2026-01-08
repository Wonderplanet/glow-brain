using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstIdleIncentiveRepository
    {
        MstIdleIncentiveModel GetMstIdleIncentive();
        IReadOnlyList<MstIdleIncentiveRewardModel> GetMstIncentiveRewards();
        IReadOnlyList<MstIdleIncentiveItemModel> GetMstIncentiveItems(MasterDataId groupId);
    }
}
