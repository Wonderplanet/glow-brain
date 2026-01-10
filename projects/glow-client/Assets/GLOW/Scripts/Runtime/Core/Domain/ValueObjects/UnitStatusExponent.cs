namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitStatusExponent(decimal Value)
    {
        public static UnitStatusExponent One => new UnitStatusExponent(1);
    }
}
