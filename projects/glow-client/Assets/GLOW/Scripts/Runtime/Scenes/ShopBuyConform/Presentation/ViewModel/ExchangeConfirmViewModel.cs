using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ShopBuyConform.Presentation.ViewModel
{
    public record ExchangeConfirmViewModel(
        ItemName ConsumptionItemName,
        ItemIconAssetPath ConsumptionItemIconAssetPath,
        ItemAmount ConsumptionAmount,
        ItemAmount ConsumptionItemBeforeAmount,
        ItemAmount ConsumptionItemAfterAmount,
        ItemName AcquisitionItemName);
}
