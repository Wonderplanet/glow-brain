using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeUserStaminaUseCaseModel(
        Stamina Stamina,
        Stamina MaxStamina,
        RemainStaminaRecoverSecond RemainFullRecoverySeconds,
        RemainStaminaRecoverSecond RemainUpdatingStaminaRecoverSecond,
        HeldAdditionalStaminaPassEffectFlag IsHeldAdditionalStaminaPassEffect)
    {
        public static HomeUserStaminaUseCaseModel Empty { get; } = new (
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
