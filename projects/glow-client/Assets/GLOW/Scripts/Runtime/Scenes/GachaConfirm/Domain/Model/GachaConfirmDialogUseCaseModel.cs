using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.GachaConfirm.Domain.Model
{
    public record GachaConfirmDialogUseCaseModel(
        MasterDataId GachaId,
        GachaType GachaType,
        MasterDataId CostId,
        CostType CostType,
        DrawableFlag DrawableFlag,
        GachaName GachaName,
        CostAmount CostAmount,
        ItemName CostName,
        GachaDrawCount GachaDrawCount,
        PlayerResourceIconAssetPath PlayerResourceIconAssetPath,
        ItemAmount PlayerItemAmount,
        FreeDiamond PlayerFreeDiamondAmount,
        FreeDiamond PlayerFreeDiamondAmountAfterConsumption,
        PaidDiamond PlayerPaidDiamondAmount,
        PaidDiamond PlayerPaidDiamondAmountAfterConsumption,
        AdGachaResetRemainingTimeSpan AdGachaResetRemainingTimeSpan,
        AdGachaDrawableCount AdGachaDrawableCount,
        HeldAdSkipPassInfoModel HeldAdSkipPassInfoModel,
        StepUpGachaCurrentStepNumber CurrentStepNumber
    )
    {
        public static GachaConfirmDialogUseCaseModel Empty { get; } = new(
            MasterDataId.Empty,
            GachaType.Normal,
            MasterDataId.Empty,
            CostType.Diamond,
            DrawableFlag.False,
            GachaName.Empty,
            CostAmount.Empty,
            ItemName.Empty,
            GachaDrawCount.Empty,
            PlayerResourceIconAssetPath.Empty,
            ItemAmount.Empty,
            FreeDiamond.Empty,
            FreeDiamond.Empty,
            PaidDiamond.Empty,
            PaidDiamond.Empty,
            AdGachaResetRemainingTimeSpan.Zero,
            AdGachaDrawableCount.Zero,
            HeldAdSkipPassInfoModel.Empty,
            StepUpGachaCurrentStepNumber.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
