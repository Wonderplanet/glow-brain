namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record SummonCost(int Value)
    {
        public static SummonCost Empty { get; } = new(-1);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
