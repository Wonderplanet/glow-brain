using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record StepupGachaPrizeStepModel(
        StepUpGachaStepNumber StepNumber,
        GachaPrizePageModel NormalPrizePageModel,
        GachaPrizePageModel FixedPrizePageModel)
    {
        public static StepupGachaPrizeStepModel Empty { get; } = new(
            StepUpGachaStepNumber.Empty,
            GachaPrizePageModel.Empty,
            GachaPrizePageModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

