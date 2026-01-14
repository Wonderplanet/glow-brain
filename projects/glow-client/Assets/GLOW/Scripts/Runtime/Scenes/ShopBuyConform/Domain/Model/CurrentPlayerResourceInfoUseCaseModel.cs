using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ShopBuyConform.Domain.Model
{
    public record CurrentPlayerResourceInfoUseCaseModel(PaidDiamond CurrentPaidDiamond,
        FreeDiamond CurrentFreeDiamond,
        Coin CurrentCoin);
}