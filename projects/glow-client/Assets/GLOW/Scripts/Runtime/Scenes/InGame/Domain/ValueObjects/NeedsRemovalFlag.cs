namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record NeedsRemovalFlag(bool Value)
    {
        public static NeedsRemovalFlag True { get; } = new(true);
        public static NeedsRemovalFlag False { get; } = new(false);

        public static implicit operator bool(NeedsRemovalFlag flag) => flag.Value;
    }
}
