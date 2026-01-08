using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.StaminaRecover
{
    public record UserDailyBuyStamina(ObscuredInt DiamondLimit, ObscuredInt AdLimit)
    {
        public static UserDailyBuyStamina Empty { get; } = new (0, 0);
    };
}
