namespace GLOW.Scenes.BoxGachaLineupDialog.Domain.ValueObject
{
    public record UnitContainInLineupFlag(bool Value)
    {
        public static UnitContainInLineupFlag True { get; } = new UnitContainInLineupFlag(true);
        public static UnitContainInLineupFlag False { get; } = new UnitContainInLineupFlag(false);
        
        public static implicit operator bool(UnitContainInLineupFlag flag) => flag.Value;
    }
}