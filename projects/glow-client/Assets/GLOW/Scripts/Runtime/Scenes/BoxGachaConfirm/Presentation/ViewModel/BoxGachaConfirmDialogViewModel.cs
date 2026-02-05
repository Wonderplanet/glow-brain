using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;

namespace GLOW.Scenes.BoxGachaConfirm.Presentation.ViewModel
{
    public record BoxGachaConfirmDialogViewModel(
        ItemName CostItemName,
        ItemIconAssetPath CostItemIconAssetPath,
        ItemAmount OfferCostItemAmount,
        CostAmount CostItemAmount,
        BoxGachaName BoxGachaName,
        GachaDrawCount CanSelectDrawCount,
        BoxGachaDrawableFlag IsDrawable)
    {
        public static BoxGachaConfirmDialogViewModel Empty { get; } = new BoxGachaConfirmDialogViewModel(
            ItemName.Empty,
            ItemIconAssetPath.Empty,
            ItemAmount.Empty,
            CostAmount.Empty,
            BoxGachaName.Empty,
            GachaDrawCount.Empty,
            BoxGachaDrawableFlag.False);
    }
}