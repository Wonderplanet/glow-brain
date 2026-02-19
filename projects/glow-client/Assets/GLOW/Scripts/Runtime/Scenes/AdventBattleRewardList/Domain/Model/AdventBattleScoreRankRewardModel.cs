using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleRewardList.Domain.ValueObject;

namespace GLOW.Scenes.AdventBattleRewardList.Domain.Model
{
    public record AdventBattleScoreRankRewardModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceModel> Rewards,
        RankType RewardRankType,
        AdventBattleScoreRankLevel RewardRankLevel,
        AdventBattleScore RewardRankLowerScore,
        AdventBattleRewardReceivedFlag DidReceiveReward) : IAdventBattlePersonalRewardModel
    {
        public static AdventBattleScoreRankRewardModel Empty { get; } = 
            new AdventBattleScoreRankRewardModel(
                MasterDataId.Empty,
                new List<PlayerResourceModel>(),
                RankType.Bronze,
                AdventBattleScoreRankLevel.Empty,
                AdventBattleScore.Empty,
                AdventBattleRewardReceivedFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        AdventBattleRewardCategory IAdventBattlePersonalRewardModel.RewardCategory =>
            AdventBattleRewardCategory.Rank;
    }
}