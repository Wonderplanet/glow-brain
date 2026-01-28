using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public record UserStaminaModel(
        Stamina CurrentStamina,
        Stamina MaxStamina,
        Stamina AdRecoverStaminaAmount,
        Stamina DiamondRecoverStaminaAmount,
        BuyStaminaAdCount RemainingAdRecoverCount,
        RecoveryStaminaMinutes RecoveryStaminaMinutes,
        RemainStaminaRecoverSecond RemainFullRecoverySeconds,
        RemainStaminaRecoverSecond RemainUpdatingStaminaRecoverSeconds,
        StaminaRecoveryFlag CanRecoverByAd,
        DateTimeOffset? StaminaUpdatedAt,
        RemainingTimeSpan RemainingAdRecoverTime,
        HeldAdditionalStaminaPassEffectFlag IsHeldAdditionalStaminaPassEffect)
    {
        public static UserStaminaModel Empty { get; } = new UserStaminaModel(
            Stamina.Empty,
            Stamina.Empty,
            Stamina.Empty,
            Stamina.Empty,
            BuyStaminaAdCount.Empty,
            RecoveryStaminaMinutes.Empty,
            RemainStaminaRecoverSecond.Empty,
            RemainStaminaRecoverSecond.Empty,
            StaminaRecoveryFlag.False,
            null,
            RemainingTimeSpan.Empty,
            HeldAdditionalStaminaPassEffectFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
