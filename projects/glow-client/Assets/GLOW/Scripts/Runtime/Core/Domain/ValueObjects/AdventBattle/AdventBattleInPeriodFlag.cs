namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleInPeriodFlag(bool Value)
    {
        public static AdventBattleInPeriodFlag True { get; } = new(true);
        public static AdventBattleInPeriodFlag False { get; } = new(false);

        public static implicit operator bool(AdventBattleInPeriodFlag flag) => flag.Value;
    }
}
