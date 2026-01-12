using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record PassDurationDay(ObscuredInt Value)
    {
        public static PassDurationDay Empty { get; } = new(0);
        public static PassDurationDay Zero { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static PassDurationDay operator -(PassDurationDay a, int b)
        {
            return new PassDurationDay(a.Value - b);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public override string ToString()
        {
            return Value.ToString();
        }
    }
}
