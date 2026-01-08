using GLOW.Scenes.StaminaBoostDialog.Domain.Model;
using GLOW.Scenes.StaminaBoostDialog.Presentation.ViewModel;

namespace GLOW.Scenes.StaminaBoostDialog.Presentation.Translator
{
    public class StaminaBoostDialogViewModelTranslator
    {
        public static StaminaBoostDialogViewModel ToViewModel(
            StaminaBoostDialogModel staminaBoostDialogModel)
        {
            return new StaminaBoostDialogViewModel(
                staminaBoostDialogModel.UserStamina,
                staminaBoostDialogModel.StageConsumeStamina,
                staminaBoostDialogModel.StaminaBoostCountLimit,
                staminaBoostDialogModel.StaminaIconAssetPath);
        }
    }
}
