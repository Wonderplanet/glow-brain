using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record BuyStaminaAdCount(ObscuredInt Value)
    {
        public static BuyStaminaAdCount Empty { get; } = new (0);
        public static BuyStaminaAdCount Zero { get; } = new (0);

        public static BuyStaminaAdCount operator -(BuyStaminaAdCount a, BuyStaminaAdCount b)
        {
            return new (a.Value - b.Value);
        }
        
        public static bool operator >(BuyStaminaAdCount a, BuyStaminaAdCount b)
        {
            return a.Value > b.Value;
        }
        
        public static bool operator <(BuyStaminaAdCount a, BuyStaminaAdCount b)
        {
            return a.Value < b.Value;
        }
        
        public static BuyStaminaAdCount Max(BuyStaminaAdCount a, BuyStaminaAdCount b)
        {
            return a.Value > b.Value ? a : b;
        }
    }
}