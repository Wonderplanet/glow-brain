using System.Collections.Generic;
using GLOW.Core.Domain.Models.Pvp;

namespace GLOW.Core.Domain.Repositories
{
    public interface IPvpReceivedRewardRepository
    {
        void SavePvpReceivedRewards(IReadOnlyList<PvpRewardModel> rewardModels);
        IReadOnlyList<PvpRewardModel> GetReceivedPvpRewardModels();
        void Clear();
    }
}

