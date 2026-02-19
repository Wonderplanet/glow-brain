using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.AdventBattle.Domain.Model
{
    public record ReceivedAdventBattleScoreRewardsModel(
        IReadOnlyList<CommonReceiveResourceModel> MaxScoreRewards,
        IReadOnlyList<CommonReceiveResourceModel> RaidTotalScoreRewards,
        AdventBattleRaidTotalScore AdventBattleRaidTotalScore)
    {
        public static ReceivedAdventBattleScoreRewardsModel Empty { get; } = new(
            new List<CommonReceiveResourceModel>(),
            new List<CommonReceiveResourceModel>(),
            AdventBattleRaidTotalScore.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
