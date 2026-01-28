using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.GachaConfirm.Presentation.ViewModels
{
    public record GachaConfirmDialogViewModel(
        MasterDataId GachaId,
        GachaType GachaType,
        MasterDataId CostId,
        GachaDrawType GachaDrawType,
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
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel
        );
}
