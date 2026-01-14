using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StaminaBoostCount(ObscuredInt Value)
    {
        public static StaminaBoostCount Empty { get; } = new(0);
        public static StaminaBoostCount One { get; } = new(1);

        public bool IsZero()
        {
            return Value == 0;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static StaminaBoostCount operator +(StaminaBoostCount a, StaminaBoostCount b)
        {
            return new(a.Value + b.Value);
        }

        public static StaminaBoostCount operator +(StaminaBoostCount a, int b)
        {
            return new(a.Value + b);
        }

        public static StaminaBoostCount operator -(StaminaBoostCount a, StaminaBoostCount b)
        {
            return new(a.Value - b.Value);
        }

        public static StaminaBoostCount operator -(StaminaBoostCount a, int b)
        {
            return new(a.Value - b);
        }

        public static bool operator >(StaminaBoostCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >(StaminaBoostCount a, StaminaBoostCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(StaminaBoostCount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <(StaminaBoostCount a, StaminaBoostCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >=(StaminaBoostCount a, int b)
        {
            return a.Value >= b;
        }

        public static bool operator >=(StaminaBoostCount a, StaminaBoostCount b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <=(StaminaBoostCount a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator <=(StaminaBoostCount a, StaminaBoostCount b)
        {
            return a.Value <= b.Value;
        }
    }
}
