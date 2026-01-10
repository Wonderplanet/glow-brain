using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleRewardModel(AdventBattleRewardCategory RewardCategory, RewardModel RewardModel)
    {
        public static AdventBattleRewardModel Empty { get; } = new(
            AdventBattleRewardCategory.Rank,
            RewardModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
