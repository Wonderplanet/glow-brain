namespace GLOW.Core.Domain.ValueObjects
{
    public record LocalNotificationIdentifier(string Value)
    {
        public static LocalNotificationIdentifier Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value;
        }
    }
}
