using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;

namespace GLOW.Scenes.StaminaRecover.Presentation.ViewModel
{
    public record StaminaListCellViewModel(
        MasterDataId MstItemId,
        ItemName Name,
        Stamina StaminaEffectValue,
        ItemAmount RequiredItemAmount,
        StaminaRecoveryAvailableStatus AvailableStatus,
        StaminaRecoveryAvailability Availability,
        RemainingTimeSpan RemainingTime,
        ItemIconAssetPath IconAssetPath);
}
