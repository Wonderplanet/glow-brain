namespace GLOW.Modules.GameOption.Domain.ValueObjects
{
    public record TwoRowDeckModeFlag(bool Value)
    {
        public static TwoRowDeckModeFlag True { get; } = new(true);
        public static TwoRowDeckModeFlag False { get; } = new(false);

        public static implicit operator bool(TwoRowDeckModeFlag flag) => flag.Value;
        public static TwoRowDeckModeFlag operator !(TwoRowDeckModeFlag flag) => new(!flag.Value);
    }
}