using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.StaminaBoostDialog.Domain.UseCase;
using GLOW.Scenes.StaminaBoostDialog.Presentation.Translator;
using GLOW.Scenes.StaminaBoostDialog.Presentation.View;
using GLOW.Scenes.StaminaBoostDialog.Presentation.ViewModel;
using Zenject;

namespace GLOW.Scenes.StaminaBoostDialog.Presentation.Presenter
{
    public class StaminaBoostDialogPresenter : IStaminaBoostDialogViewDelegate
    {
        [Inject] StaminaBoostDialogViewController ViewController { get; }
        [Inject] StaminaBoostDialogViewController.Argument Argument { get; }
        [Inject] StaminaBoostUseCase StaminaBoostUseCase { get; }

        void IStaminaBoostDialogViewDelegate.OnViewWillAppear()
        {
            var model = StaminaBoostUseCase.GetStaminaBoostDialogModel(Argument.StageId);
            var viewModel = StaminaBoostDialogViewModelTranslator.ToViewModel(model);

            ViewController.Setup(viewModel);
        }

        void IStaminaBoostDialogViewDelegate.OnStartButtonTapped(bool isEnoughStamina, StaminaBoostCount staminaSelectCount)
        {
            // ボタン連打防止
            ViewController.SetButtonInteractable(false);
            ViewController.OnStartButtonTappedAction?.Invoke(isEnoughStamina, staminaSelectCount);
        }

        void IStaminaBoostDialogViewDelegate.OnCancelButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}
