using System.Linq;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.StepupGachaRatioDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.StepupGachaRatioDialog.Presentation.Presenters
{
    public class StepupGachaRatioDialogPresenter : IStepupGachaRatioDialogViewDelegate
    {
        [Inject] StepupGachaRatioDialogViewController ViewController { get; }
        [Inject] StepupGachaRatioDialogViewController.Argument Args { get; }
        [Inject] GachaWireFrame.Presentation.Presenters.GachaWireFrame GachaWireFrame { get; }

        void IStepupGachaRatioDialogViewDelegate.OnViewDidLoad()
        {
            ViewController.Args = Args;
            ViewController.Setup(Args.ViewModel);

            var firstStep = Args.ViewModel.StepViewModels.FirstOrDefault();
            if (firstStep != null)
            {
                ViewController.StepRatioPageSetUp(firstStep);
            }
        }

        void IStepupGachaRatioDialogViewDelegate.OnClosed()
        {
            GachaWireFrame.OnCloseStepupGachaRatioDialogViewAndInvokeAction();
        }

        void IStepupGachaRatioDialogViewDelegate.OnNormalPrizeTabSelected()
        {
            ViewController.SwitchToNormalPrizeTab();
        }

        void IStepupGachaRatioDialogViewDelegate.OnFixedPrizeTabSelected()
        {
            ViewController.SwitchToFixedPrizeTab();
        }

        void IStepupGachaRatioDialogViewDelegate.OnStepSelected(StepUpGachaStepNumber stepNumber)
        {
            var stepViewModel = Args.ViewModel.StepViewModels
                .FirstOrDefault(step => step.StepNumber == stepNumber);
            if (stepViewModel == null) return;

            ViewController.StepRatioPageUpdate(stepViewModel);
        }
    }
}
