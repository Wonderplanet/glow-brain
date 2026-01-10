namespace GLOW.Core.Domain.ValueObjects
{
    public record ReceivedFlag(bool Value)
    {
        public static ReceivedFlag Empty { get; } = new(false);
    }
}
