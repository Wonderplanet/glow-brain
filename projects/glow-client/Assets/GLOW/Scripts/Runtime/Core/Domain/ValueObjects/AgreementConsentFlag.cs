namespace GLOW.Core.Domain.ValueObjects
{
    public record AgreementConsentFlag(bool Value)
    {
        public static AgreementConsentFlag Empty { get; } = new(false);
        public static AgreementConsentFlag True { get; } = new(true);
        public static AgreementConsentFlag False { get; } = new(false);

        public static implicit operator bool(AgreementConsentFlag flag) => flag.Value;

        public static AgreementConsentFlag IntToFlag(int value)
        {
            return value switch
            {
                0 => False,
                1 => True,
                _ => Empty
            };
        }
    }
}
