using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.StaminaBoostDialog.Presentation.View
{
    public interface IStaminaBoostDialogViewDelegate
    {
        void OnViewWillAppear();
        void OnStartButtonTapped(bool isEnoughStamina, StaminaBoostCount staminaSelectCount);
        void OnCancelButtonTapped();
    }
}
