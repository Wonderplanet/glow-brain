using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRewardList.Domain.ValueObject;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel
{
    public record AdventBattleRaidTotalScoreRewardCellViewModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceIconViewModel> Rewards,
        AdventBattleRewardCondition RewardCondition,
        AdventBattleRewardReceivedFlag DidReceiveReward)
    {
        public static AdventBattleRaidTotalScoreRewardCellViewModel Empty { get; } = 
            new AdventBattleRaidTotalScoreRewardCellViewModel(
                MasterDataId.Empty,
                new List<PlayerResourceIconViewModel>(),
                AdventBattleRewardCondition.Empty,
                AdventBattleRewardReceivedFlag.False);
    }
}