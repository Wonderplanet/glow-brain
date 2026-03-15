using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.OprData
{
    public record OprStepUpGachaStepModel(
        MasterDataId OprGachaId,
        StepUpGachaStepNumber StepNumber,
        CostType CostType,
        MasterDataId MstCostId,
        CostAmount CostAmount,
        GachaDrawCount DrawCount,
        GachaFixedPrizeDescription FixedPrizeDescription,
        IsFirstFreeFlag IsFirstFree)
    {
        public static OprStepUpGachaStepModel Empty { get; } = new(
            MasterDataId.Empty,
            StepUpGachaStepNumber.Empty,
            CostType.Coin,
            MasterDataId.Empty,
            CostAmount.Zero,
            GachaDrawCount.Zero,
            GachaFixedPrizeDescription.Empty,
            IsFirstFreeFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

