namespace GLOW.Core.Domain.ValueObjects
{
    public record ShouldShowParentalConsentFlag(bool Value)
    {
        public bool IsTrue()
        {
            return Value;
        }
    }
}
