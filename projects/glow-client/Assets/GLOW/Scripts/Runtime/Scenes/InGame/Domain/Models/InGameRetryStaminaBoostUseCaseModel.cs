using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record InGameRetryStaminaBoostUseCaseModel(
        MasterDataId StageId,
        StaminaBoostFlag IsStaminaBoostAvailable,
        EnoughStaminaFlag IsEnoughStamina
        );
}