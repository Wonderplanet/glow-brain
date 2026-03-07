using System.Collections.Generic;

namespace GLOW.Scenes.StepupGachaRatioDialog.Presentation.ViewModels
{
    public record StepupGachaRatioDialogViewModel(
        IReadOnlyList<StepupGachaRatioStepViewModel> StepViewModels);
}

