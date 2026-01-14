namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AutoPlayerEnabledFlag(bool Value)
    {
        public static AutoPlayerEnabledFlag True { get; } = new(true);
        public static AutoPlayerEnabledFlag False { get; } = new(false);

        public static implicit operator bool(AutoPlayerEnabledFlag flag) => flag.Value;
    }
}