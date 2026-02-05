using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.StaminaBoostDialog.Presentation.ViewModel
{
    public record StaminaBoostDialogViewModel(
        Stamina UserStamina,
        StageConsumeStamina StageConsumeStamina,
        StaminaBoostCount StaminaBoostCountLimit,
        StaminaIconAssetPath StaminaIconAssetPath);
}
