namespace GLOW.Core.Domain.ValueObjects
{
    public record AgreementBnLogoDisplayFlag(bool Value)
    {
        public static AgreementBnLogoDisplayFlag Empty { get; } = new(false);
        public static AgreementBnLogoDisplayFlag True { get; } = new(true);
        public static AgreementBnLogoDisplayFlag False { get; } = new(false);

        public static implicit operator bool(AgreementBnLogoDisplayFlag flag) => flag.Value;

        public int ToInt()
        {
            return Value ? 1 : 0;
        }
    }
}
