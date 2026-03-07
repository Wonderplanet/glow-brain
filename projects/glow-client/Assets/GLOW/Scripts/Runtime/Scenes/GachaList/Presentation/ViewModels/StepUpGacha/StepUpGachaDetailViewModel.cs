using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels.StepUpGacha
{
    public record StepUpGachaDetailViewModel(
        StepUpGachaStepNumber StepNumber,
        CostType CostType,
        MasterDataId MstCostId,
        CostAmount CostAmount,
        GachaDrawCount DrawCount,
        PlayerResourceIconAssetPath CostIconAssetPath,
        GachaFixedPrizeDescription FixedPrizeDescription,
        GachaFreeDrawFlag IsFree,
        // おまけ報酬表示用
        ResourceType OmakeResourceType,
        ExistStepUpGachaOmakeFlag HasOmake,
        PlayerResourceIconAssetPath OmakeIconAssetPath,
        ItemAmount OmakeAmount)
    {
        public static StepUpGachaDetailViewModel Empty { get; } = new(
            StepUpGachaStepNumber.Empty,
            CostType.Coin,
            MasterDataId.Empty,
            CostAmount.Zero,
            GachaDrawCount.Zero,
            PlayerResourceIconAssetPath.Empty,
            GachaFixedPrizeDescription.Empty,
            GachaFreeDrawFlag.False,
            ResourceType.Coin,
            ExistStepUpGachaOmakeFlag.False,
            PlayerResourceIconAssetPath.Empty,
            ItemAmount.Zero);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

