using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeHeaderStaminaViewModel(
        Stamina Stamina, 
        Stamina MaxStamina, 
        RemainStaminaRecoverSecond RemainFullRecoverySeconds,
        RemainStaminaRecoverSecond RemainUpdatingStaminaRecoverSeconds,
        HeldAdditionalStaminaPassEffectFlag IsHeldAdditionalStaminaPassEffect)
    {
        public static HomeHeaderStaminaViewModel Empty { get; } = new (
            Stamina.Empty,
            Stamina.Empty,
            RemainStaminaRecoverSecond.Empty,
            RemainStaminaRecoverSecond.Empty,
            HeldAdditionalStaminaPassEffectFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
