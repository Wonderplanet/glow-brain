using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.StaminaBoostDialog.Domain.Model
{
    public record StaminaBoostDialogModel(
        Stamina UserStamina,
        StageConsumeStamina StageConsumeStamina,
        StaminaBoostCount StaminaBoostCountLimit,
        StaminaIconAssetPath StaminaIconAssetPath);
}
