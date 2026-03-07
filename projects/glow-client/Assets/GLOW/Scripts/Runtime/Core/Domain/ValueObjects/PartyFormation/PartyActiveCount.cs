namespace GLOW.Core.Domain.ValueObjects
{
    public record PartyActiveCount(int Value)
    {
        public static PartyActiveCount Empty { get; } = new(0);
        public static PartyActiveCount Max { get; } = new(10);
    }
}
