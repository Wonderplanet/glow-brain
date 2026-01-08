using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.StaminaBoostDialog.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.StaminaBoostDialog.Presentation.View
{
    public class StaminaBoostDialogViewController : UIViewController<StaminaBoostDialogView>
    {
        public record Argument(MasterDataId StageId);
        public Action<bool, StaminaBoostCount> OnStartButtonTappedAction;

        [Inject] IStaminaBoostDialogViewDelegate ViewDelegate { get; }

        StaminaBoostDialogViewModel _viewModel;
        bool _isEnoughStamina = false;
        StaminaBoostCount _currentStaminaBoostCount = StaminaBoostCount.Empty;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            _currentStaminaBoostCount = StaminaBoostCount.One;
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void Setup(StaminaBoostDialogViewModel viewModel)
        {
            _viewModel = viewModel;

            var boostedConsumeStamina = viewModel.StageConsumeStamina * _currentStaminaBoostCount.Value;
            var afterStamina = viewModel.UserStamina - boostedConsumeStamina.Value;
            _isEnoughStamina = afterStamina.Value >= 0;
            ActualView.SetStaminaIcon(viewModel.StaminaIconAssetPath);
            ActualView.SetCurrentStaminaText(_viewModel.UserStamina);
            ActualView.SetConsumeStaminaText(boostedConsumeStamina);
            ActualView.SetAfterStaminaText(afterStamina);

            ActualView.SetAmountSelection(_currentStaminaBoostCount, viewModel.StaminaBoostCountLimit, () =>
            {
                _currentStaminaBoostCount = new StaminaBoostCount(ActualView.SelectedItemAmount.Value);
                var updateBoostedConsumeStamina = _viewModel.StageConsumeStamina * ActualView.SelectedItemAmount.Value;
                var updateAfterStamina = _viewModel.UserStamina - updateBoostedConsumeStamina.Value;
                _isEnoughStamina = updateAfterStamina.Value >= 0;
                ActualView.SetConsumeStaminaText(updateBoostedConsumeStamina);
                ActualView.SetAfterStaminaText(updateAfterStamina);
            });

            // ボタンのインタラクティブをオンにする
            ActualView.SetButtonInteractable(true);
        }

        public void SetButtonInteractable(bool isInteractable)
        {
            ActualView.SetButtonInteractable(isInteractable);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCancelButtonTapped();
        }

        [UIAction]
        void OnStartButtonTapped()
        {
            ViewDelegate.OnStartButtonTapped(_isEnoughStamina, new StaminaBoostCount(ActualView.SelectedItemAmount.Value));
        }
    }
}
