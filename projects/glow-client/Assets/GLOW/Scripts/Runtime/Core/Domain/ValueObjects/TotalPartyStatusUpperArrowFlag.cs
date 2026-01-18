namespace GLOW.Core.Domain.ValueObjects
{
    public record TotalPartyStatusUpperArrowFlag(bool Value)
    {
        public static TotalPartyStatusUpperArrowFlag True { get; } = new TotalPartyStatusUpperArrowFlag(true);
        public static TotalPartyStatusUpperArrowFlag False { get; } = new TotalPartyStatusUpperArrowFlag(false);

        public static implicit operator bool(TotalPartyStatusUpperArrowFlag totalPartyStatusUpperArrowFlag)
            => totalPartyStatusUpperArrowFlag.Value;
    }
}
