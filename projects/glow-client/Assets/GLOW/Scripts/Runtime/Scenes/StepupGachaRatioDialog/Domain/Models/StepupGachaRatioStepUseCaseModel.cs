using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaRatio.Domain.Model;

namespace GLOW.Scenes.StepupGachaRatioDialog.Domain.Models
{
    public record StepupGachaRatioStepUseCaseModel(
        StepUpGachaStepNumber StepNumber,
        GachaFixedPrizeDescription GachaFixedPrizeDescription,
        GachaRatioPageModel NormalPrizePageModel,
        GachaRatioPageModel FixedPrizePageModel)
    {
        public static StepupGachaRatioStepUseCaseModel Empty { get; } = new(
            StepUpGachaStepNumber.Empty,
            GachaFixedPrizeDescription.Empty,
            GachaRatioPageModel.Empty,
            GachaRatioPageModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

