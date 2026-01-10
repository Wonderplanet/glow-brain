using System.Collections.Generic;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCaseModel
{
    public record ExchangeShopUseCaseModel(
        ExchangeShopName Name,
        IReadOnlyList<ExchangeShopCellUseCaseModel> CellUseCaseModels,
        IReadOnlyList<ExchangeShopTopAmountModel> TradeAmountModels)
    {
        public static ExchangeShopUseCaseModel Empty { get; } = new ExchangeShopUseCaseModel(
            new ExchangeShopName(""),
            new List<ExchangeShopCellUseCaseModel>(),
            new List<ExchangeShopTopAmountModel>());
    }
}
