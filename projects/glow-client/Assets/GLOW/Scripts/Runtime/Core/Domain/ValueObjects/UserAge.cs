using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UserAge(ObscuredInt Value)
    {
        public static UserAge Empty { get; } = new (-1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static bool operator <(UserAge a, UserAge b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >(UserAge a, UserAge b)
        {
            return a.Value > b.Value;
        }
    };
}
