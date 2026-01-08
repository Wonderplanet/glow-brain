using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.StaminaRecover.Domain.ValueObject
{
    public record StaminaShortageFlag(ObscuredBool IsStaminaShortage)
    {
        public static readonly StaminaShortageFlag True = new StaminaShortageFlag(true);
        public static readonly StaminaShortageFlag False = new StaminaShortageFlag(false);

        public static implicit operator bool(StaminaShortageFlag flag) => flag.IsStaminaShortage;
    }
}
