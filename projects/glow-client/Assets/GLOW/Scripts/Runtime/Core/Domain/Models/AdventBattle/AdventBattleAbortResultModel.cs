using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleAbortResultModel(AdventBattleRaidTotalScore TotalScore)
    {
        public static AdventBattleAbortResultModel Empty { get; } = new(AdventBattleRaidTotalScore.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
