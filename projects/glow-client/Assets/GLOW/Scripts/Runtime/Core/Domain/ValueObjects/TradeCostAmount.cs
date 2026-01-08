using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record TradeCostAmount(ObscuredInt Value)
    {
        public static TradeCostAmount Empty { get; } = new TradeCostAmount(0);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public ItemAmount ToItemAmount()
        {
            return new(Value);
        }
    }
}