namespace GLOW.Scenes.Home.Domain.ValueObjects
{
    public record AcquiredFlag(bool Value)
    {
        public static AcquiredFlag True { get; } = new AcquiredFlag(true);
        public static AcquiredFlag False { get; } = new AcquiredFlag(false);

        public static implicit operator bool(AcquiredFlag flag) => flag.Value;
    }
}
