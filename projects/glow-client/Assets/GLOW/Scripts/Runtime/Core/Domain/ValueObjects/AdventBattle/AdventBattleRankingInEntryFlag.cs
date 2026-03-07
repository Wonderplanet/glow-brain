namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleRankingInEntryFlag(bool Value)
    {
        public static AdventBattleRankingInEntryFlag True { get; } = new(true);
        public static AdventBattleRankingInEntryFlag False { get; } = new(false);

        public static implicit operator bool(AdventBattleRankingInEntryFlag flag) => flag.Value;
    }
}