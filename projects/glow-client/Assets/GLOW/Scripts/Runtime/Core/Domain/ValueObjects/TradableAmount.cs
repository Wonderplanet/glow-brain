using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record TradableAmount(ObscuredInt Value)
    {
        public static TradableAmount Empty { get; } = new TradableAmount(0);
        
        public static TradableAmount Zero { get; } = new TradableAmount(0);
        
        public static TradableAmount Infinity { get; } = new TradableAmount(int.MaxValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsInfinity()
        {
            return ReferenceEquals(this, Infinity);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public static TradableAmount operator -(TradableAmount a, TradableAmount b)
        {
            return new TradableAmount(a.Value - b.Value);
        }
        
        public static TradableAmount operator /(TradableAmount a, TradeCostAmount b)
        {
            return new TradableAmount(a.Value / b.Value);
        }
        
        public static TradableAmount Min(TradableAmount a, TradableAmount b)
        {
            return a.Value <= b.Value ? a : b;
        }
        
        public static TradableAmount Max(TradableAmount a, TradableAmount b)
        {
            return a.Value >= b.Value ? a : b;
        }
        
        public static bool operator <(TradableAmount a, ItemAmount b)
        {
            return a.Value < b.Value;
        }
        
        public static bool operator >(TradableAmount a, ItemAmount b)
        {
            return a.Value > b.Value;
        }

        
        public ItemAmount ToItemAmount()
        {
            return new ItemAmount(Value);
        }
    }
}