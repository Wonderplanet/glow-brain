using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.OprData
{
    public record OprStepUpGachaStepRewardModel(
        MasterDataId OprGachaId,
        StepUpGachaStepNumber StepNumber,
        StepUpGachaLoopCountTarget LoopCountTarget,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ItemAmount ResourceAmount)
    {
        public static OprStepUpGachaStepRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            StepUpGachaStepNumber.Empty,
            StepUpGachaLoopCountTarget.Empty,
            ResourceType.Coin,
            MasterDataId.Empty,
            ItemAmount.Zero);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

