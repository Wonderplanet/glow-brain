using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaContent.Domain.Model
{
    public record StepUpGachaStepUseCaseModel(
        StepUpGachaStepNumber StepNumber,
        CostType CostType,
        MasterDataId MstCostId,
        CostAmount CostAmount,
        GachaDrawCount DrawCount,
        PlayerResourceIconAssetPath CostIconAssetPath,
        GachaFixedPrizeDescription FixedPrizeDescription,
        GachaFreeDrawFlag IsFree,
        IReadOnlyList<StepUpGachaStepRewardUseCaseModel> StepRewards)
    {
        public static StepUpGachaStepUseCaseModel Empty { get; } = new(
            StepUpGachaStepNumber.Empty,
            CostType.Free,
            MasterDataId.Empty,
            CostAmount.Zero,
            GachaDrawCount.Zero,
            PlayerResourceIconAssetPath.Empty,
            GachaFixedPrizeDescription.Empty,
            GachaFreeDrawFlag.False,
            new List<StepUpGachaStepRewardUseCaseModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

