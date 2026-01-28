using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record CurrentMonthTotalBilling(ObscuredInt Value)
    {
        public static CurrentMonthTotalBilling Empty { get; } = new (-1);

        public static bool operator <(CurrentMonthTotalBilling a, int b)
        {
            return a.Value < b;
        }
        public static bool operator >(CurrentMonthTotalBilling a, int b)
        {
            return a.Value > b;
        }

        public static bool operator <=(CurrentMonthTotalBilling a, int b)
        {
            return a.Value <= b;
        }
        public static bool operator >=(CurrentMonthTotalBilling a, int b)
        {
            return a.Value >= b;
        }
    };
}
