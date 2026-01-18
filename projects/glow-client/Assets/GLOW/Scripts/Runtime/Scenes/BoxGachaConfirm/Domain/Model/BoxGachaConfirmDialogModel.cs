using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;

namespace GLOW.Scenes.BoxGachaConfirm.Domain.Model
{
    public record BoxGachaConfirmDialogModel(
        ItemName CostItemName,
        ItemIconAssetPath CostItemIconAssetPath,
        ItemAmount OfferCostItemAmount,
        CostAmount CostItemAmount,
        BoxGachaName BoxGachaName,
        GachaDrawCount CanSelectDrawCount,
        BoxGachaDrawableFlag IsDrawable)
    {
        public static BoxGachaConfirmDialogModel Empty { get; } = new BoxGachaConfirmDialogModel(
            ItemName.Empty,
            ItemIconAssetPath.Empty,
            ItemAmount.Empty,
            CostAmount.Empty,
            BoxGachaName.Empty,
            GachaDrawCount.Empty,
            BoxGachaDrawableFlag.False);
    }
}