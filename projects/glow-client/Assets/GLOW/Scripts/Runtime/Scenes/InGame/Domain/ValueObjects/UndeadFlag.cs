namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record UndeadFlag(bool Value)
    {
        public static UndeadFlag True { get; } = new(true);
        public static UndeadFlag False { get; } = new(false);

        public static implicit operator bool(UndeadFlag flag) => flag.Value;
    }
}
