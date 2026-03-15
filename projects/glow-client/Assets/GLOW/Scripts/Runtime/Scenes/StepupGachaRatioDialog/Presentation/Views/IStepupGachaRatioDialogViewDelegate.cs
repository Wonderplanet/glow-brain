using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.StepupGachaRatioDialog.Presentation.Views
{
    public interface IStepupGachaRatioDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnClosed();
        void OnNormalPrizeTabSelected();
        void OnFixedPrizeTabSelected();
        void OnStepSelected(StepUpGachaStepNumber stepNumber);
    }
}
