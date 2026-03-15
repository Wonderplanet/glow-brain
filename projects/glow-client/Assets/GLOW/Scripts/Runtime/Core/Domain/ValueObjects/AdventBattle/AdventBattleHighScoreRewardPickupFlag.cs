namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleHighScoreRewardPickupFlag(bool Value)
    {
        public static AdventBattleHighScoreRewardPickupFlag True { get; } = new AdventBattleHighScoreRewardPickupFlag(true);
        public static AdventBattleHighScoreRewardPickupFlag False { get; } = new AdventBattleHighScoreRewardPickupFlag(false);

        public static implicit operator bool(AdventBattleHighScoreRewardPickupFlag flag) => flag.Value;
    }
}