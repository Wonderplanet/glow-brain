using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.StaminaRecover.Domain.UseCaseModel
{
    public record StaminaTradeUseCaseModel(
        MasterDataId MstItemId,
        ItemName Name,
        Stamina EffectValue,
        Stamina CurrentUserStamina,
        PurchasableCount MaxPurchasableCount,
        PlayerResourceModel ItemIconModel,
        Stamina MaxStamina)
    {
        public static StaminaTradeUseCaseModel Empty { get; } = new StaminaTradeUseCaseModel(
            MasterDataId.Empty,
            ItemName.Empty,
            Stamina.Empty,
            Stamina.Empty,
            PurchasableCount.Empty,
            PlayerResourceModel.Empty,
            Stamina.Empty);
    }
}
