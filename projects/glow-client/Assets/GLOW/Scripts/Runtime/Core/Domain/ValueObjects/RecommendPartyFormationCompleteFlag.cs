namespace GLOW.Core.Domain.ValueObjects
{
    public record RecommendPartyFormationCompleteFlag(bool Value)
    {
        public static RecommendPartyFormationCompleteFlag Empty { get; } = new(false);
        public static RecommendPartyFormationCompleteFlag True { get; } = new(true);
        public static RecommendPartyFormationCompleteFlag False { get; } = new(false);

        public static implicit operator bool(RecommendPartyFormationCompleteFlag flag) => flag.Value;
    }
}
