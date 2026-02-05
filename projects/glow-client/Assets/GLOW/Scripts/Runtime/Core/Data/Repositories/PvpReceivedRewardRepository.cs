using System.Collections.Generic;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;

namespace GLOW.Core.Data.Repositories
{
    public class PvpReceivedRewardRepository : IPvpReceivedRewardRepository
    {
        IReadOnlyList<PvpRewardModel> _receivedPvpRewardModels = new List<PvpRewardModel>();

        void IPvpReceivedRewardRepository.SavePvpReceivedRewards(
            IReadOnlyList<PvpRewardModel> rewardModels)
        {
            _receivedPvpRewardModels = rewardModels;
        }

        IReadOnlyList<PvpRewardModel> IPvpReceivedRewardRepository.GetReceivedPvpRewardModels()
        {
            return _receivedPvpRewardModels;
        }

        void IPvpReceivedRewardRepository.Clear()
        {
            _receivedPvpRewardModels = new List<PvpRewardModel>();
        }
    }
}
