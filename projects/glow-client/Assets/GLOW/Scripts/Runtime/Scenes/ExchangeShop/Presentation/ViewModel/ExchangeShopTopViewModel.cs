using System.Collections.Generic;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Scenes.ExchangeShop.Presentation.ViewModel
{
    public record ExchangeShopTopViewModel(
        ExchangeShopName Name,
        IReadOnlyList<ExchangeShopCellViewModel> CellViewModels,
        IReadOnlyList<ExchangeShopTopAmountViewModel> ExchangeShopTopAmountViewModels)
    {
        public static ExchangeShopTopViewModel Empty { get; } = new ExchangeShopTopViewModel(
            new ExchangeShopName(""),
            new List<ExchangeShopCellViewModel>(),
            new List<ExchangeShopTopAmountViewModel>());
    }
}
