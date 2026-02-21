using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record UnlockOrder(ObscuredInt Value)
    {
        public static UnlockOrder Empty { get; } = new(0);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public static bool operator >(UnlockOrder a, UnlockOrder b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(UnlockOrder a, UnlockOrder b)
        {
            return a.Value >= b.Value;
        }
        
        public static bool operator <(UnlockOrder a, UnlockOrder b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(UnlockOrder a, UnlockOrder b)
        {
            return a.Value <= b.Value;
        }
    }
}