using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;

namespace GLOW.Scenes.StepupGachaRatioDialog.Presentation.ViewModels
{
    public record StepupGachaRatioStepViewModel(
        StepUpGachaStepNumber StepNumber,
        GachaFixedPrizeDescription GachaFixedPrizeDescription,
        GachaRatioPageViewModel NormalPrizePageViewModel,
        GachaRatioPageViewModel FixedPrizePageViewModel)
    {
        public static StepupGachaRatioStepViewModel Empty { get; } = new(
            StepUpGachaStepNumber.Empty,
            GachaFixedPrizeDescription.Empty,
            GachaRatioPageViewModel.Empty,
            GachaRatioPageViewModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

