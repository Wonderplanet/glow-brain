namespace GLOW.Core.Domain.ValueObjects
{
    public record EnoughItem(bool Value)
    {
        public static implicit operator bool(EnoughItem item) => item.Value;
    }
}
