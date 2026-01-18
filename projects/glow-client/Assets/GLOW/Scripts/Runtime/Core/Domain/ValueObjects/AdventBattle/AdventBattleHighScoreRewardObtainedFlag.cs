namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleHighScoreRewardObtainedFlag(bool Value)
    {
        public static AdventBattleHighScoreRewardObtainedFlag True => new AdventBattleHighScoreRewardObtainedFlag(true);
        public static AdventBattleHighScoreRewardObtainedFlag False => new AdventBattleHighScoreRewardObtainedFlag(false);

        public static implicit operator bool(AdventBattleHighScoreRewardObtainedFlag flag) => flag.Value;
    }
}