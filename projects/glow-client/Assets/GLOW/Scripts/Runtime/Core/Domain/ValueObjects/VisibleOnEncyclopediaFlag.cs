namespace GLOW.Core.Domain.ValueObjects
{
    public record VisibleOnEncyclopediaFlag(bool Value)
    {
        public static VisibleOnEncyclopediaFlag Empty { get; } = new(false);
        public static VisibleOnEncyclopediaFlag True { get; } = new(true);
        public static VisibleOnEncyclopediaFlag False { get; } = new(false);

        public static implicit operator bool(VisibleOnEncyclopediaFlag flag) => flag.Value;
    }
}
