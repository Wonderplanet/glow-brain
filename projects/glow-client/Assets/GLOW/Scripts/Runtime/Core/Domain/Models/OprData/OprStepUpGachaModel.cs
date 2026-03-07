using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.OprData
{
    public record OprStepUpGachaModel(
        MasterDataId OprGachaId,
        StepUpGachaMaxStepNumber MaxStepNumber,
        StepUpGachaMaxLoopCount MaxLoopCount)
    {
        public static OprStepUpGachaModel Empty { get; } = new(
            MasterDataId.Empty,
            StepUpGachaMaxStepNumber.Empty,
            StepUpGachaMaxLoopCount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

