using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;

namespace GLOW.Scenes.StaminaRecover.Domain.UseCaseModel
{
    public record StaminaRecoverySelectCellUseCaseModel(
        MasterDataId MstItemId,
        ItemName Name,
        Stamina StaminaEffectValue,
        ItemAmount RequiredItemAmount,
        StaminaRecoveryAvailableStatus AvailableStatus,
        StaminaRecoveryAvailability Availability,
        RemainingTimeSpan RemainingTime,
        ItemAssetKey ItemAssetKey)
    {
        public static StaminaRecoverySelectCellUseCaseModel Empty { get; } = new (
            MasterDataId.Empty,
            ItemName.Empty,
            Stamina.Empty,
            ItemAmount.Empty,
            StaminaRecoveryAvailableStatus.Empty,
            StaminaRecoveryAvailability.Unavailable,
            RemainingTimeSpan.Empty,
            ItemAssetKey.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
