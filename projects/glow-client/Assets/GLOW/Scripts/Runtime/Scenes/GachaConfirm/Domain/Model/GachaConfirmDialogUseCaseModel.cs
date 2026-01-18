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
        HeldAdSkipPassInfoModel HeldAdSkipPassInfoModel
    );
}
