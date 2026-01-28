using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRewardList.Domain.ValueObject;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel
{
    public record AdventBattleScoreRankRewardCellViewModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceIconViewModel> Rewards,
        RankType RewardRankType,
        AdventBattleScoreRankLevel RewardRankLevel,
        AdventBattleScore RewardRankLowerScore,
        AdventBattleRewardReceivedFlag DidReceiveReward) : IAdventBattlePersonalCellViewModel
    {
        public static AdventBattleScoreRankRewardCellViewModel Empty { get; } = 
            new AdventBattleScoreRankRewardCellViewModel(
                MasterDataId.Empty,
                new List<PlayerResourceIconViewModel>(),
                RankType.Bronze,
                AdventBattleScoreRankLevel.Empty,
                AdventBattleScore.Empty,
                AdventBattleRewardReceivedFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        AdventBattleRewardCategory IAdventBattlePersonalCellViewModel.RewardCategory =>
            AdventBattleRewardCategory.Rank;
    }
}