using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.StepupGachaRatioDialog.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.StepupGachaRatioDialog.Presentation.Views
{
    public class StepupGachaRatioDialogViewController : UIViewController<StepupGachaRatioDialogView>, IEscapeResponder
    {
        public record Argument(MasterDataId OprGachaId, StepupGachaRatioDialogViewModel ViewModel);

        [Inject] IStepupGachaRatioDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        StepUpGachaStepNumber _currentStepNumber = StepUpGachaStepNumber.Empty;
        bool _isNormalPrizeTabSelected = true;

        public StepUpGachaStepNumber CurrentStepNumber => _currentStepNumber;
        public bool IsNormalPrizeTabSelected => _isNormalPrizeTabSelected;

        public StepupGachaRatioDialogViewController.Argument Args { get; set; }
        public float NormalizedPos => ActualView.ScrollRect.verticalNormalizedPosition;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void Setup(StepupGachaRatioDialogViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        public void StepRatioPageSetUp(StepupGachaRatioStepViewModel stepViewModel)
        {
            ActualView.SwitchStepRatioPage(stepViewModel);
            ActualView.SwitchPrizeTypeTab(isNormalPrize: true);
            _currentStepNumber = stepViewModel.StepNumber;
            _isNormalPrizeTabSelected = true;
        }

        public void StepRatioPageUpdate(StepupGachaRatioStepViewModel stepViewModel)
        {
            if (_currentStepNumber == stepViewModel.StepNumber) return;

            ActualView.SwitchStepRatioPage(stepViewModel);
            _currentStepNumber = stepViewModel.StepNumber;
            _isNormalPrizeTabSelected = true;
        }

        public void SwitchToNormalPrizeTab()
        {
            if (_isNormalPrizeTabSelected) return;

            ActualView.SwitchPrizeTypeTab(isNormalPrize: true);
            _isNormalPrizeTabSelected = true;
        }

        public void SwitchToFixedPrizeTab()
        {
            if (!_isNormalPrizeTabSelected) return;

            ActualView.SwitchPrizeTypeTab(isNormalPrize: false);
            _isNormalPrizeTabSelected = false;
        }

        public void SwitchPrizeTypeTab(bool isNormalPrize)
        {
            if (isNormalPrize)
            {
                SwitchToNormalPrizeTab();
            }
            else
            {
                SwitchToFixedPrizeTab();
            }
        }

        public void MoveScrollToTargetPos(float targetPos)
        {
            ActualView.MoveScrollToTargetPos(targetPos);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            Close();
            return true;
        }

        void Close()
        {
            ViewDelegate.OnClosed();
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            Close();
        }

        [UIAction]
        public void OnNormalPrizeTabTapped()
        {
            ViewDelegate.OnNormalPrizeTabSelected();
        }

        [UIAction]
        public void OnFixedPrizeTabTapped()
        {
            ViewDelegate.OnFixedPrizeTabSelected();
        }

        [UIAction]
        public void OnStep1ButtonTapped()
        {
            var stepViewModel = ActualView.GetStepViewModelByIndex(0);
            if (stepViewModel == null) return;

            ViewDelegate.OnStepSelected(stepViewModel.StepNumber);
        }

        [UIAction]
        public void OnStep2ButtonTapped()
        {
            var stepViewModel = ActualView.GetStepViewModelByIndex(1);
            if (stepViewModel == null) return;

            ViewDelegate.OnStepSelected(stepViewModel.StepNumber);
        }

        [UIAction]
        public void OnStep3ButtonTapped()
        {
            var stepViewModel = ActualView.GetStepViewModelByIndex(2);
            if (stepViewModel == null) return;

            ViewDelegate.OnStepSelected(stepViewModel.StepNumber);
        }

        [UIAction]
        public void OnStep4ButtonTapped()
        {
            var stepViewModel = ActualView.GetStepViewModelByIndex(3);
            if (stepViewModel == null) return;

            ViewDelegate.OnStepSelected(stepViewModel.StepNumber);
        }

        [UIAction]
        public void OnStep5ButtonTapped()
        {
            var stepViewModel = ActualView.GetStepViewModelByIndex(4);
            if (stepViewModel == null) return;

            ViewDelegate.OnStepSelected(stepViewModel.StepNumber);
        }
    }
}
