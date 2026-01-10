using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleRewardList.Domain.ValueObject;

namespace GLOW.Scenes.AdventBattleRewardList.Domain.Model
{
    public record AdventBattleRaidTotalScoreRewardModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceModel> Rewards,
        AdventBattleRewardCondition RewardCondition,
        AdventBattleRewardReceivedFlag DidReceiveReward)
    {
        public static AdventBattleRaidTotalScoreRewardModel Empty { get; } = 
            new AdventBattleRaidTotalScoreRewardModel(
                MasterDataId.Empty,
                new List<PlayerResourceModel>(),
                AdventBattleRewardCondition.Empty,
                AdventBattleRewardReceivedFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}