using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.StaminaRecover.Presentation.ViewModel
{
    public record StaminaTradeViewModel(
        MasterDataId MstItemId,
        ItemName Name,
        Stamina EffectValue,
        Stamina CurrentUserStamina,
        PurchasableCount MaxPurchasableCount,
        PlayerResourceIconViewModel ItemIconViewModel,
        Stamina MaxStamina
        );
}
