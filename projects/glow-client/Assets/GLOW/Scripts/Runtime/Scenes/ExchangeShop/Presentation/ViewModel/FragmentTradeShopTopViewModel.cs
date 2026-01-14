using System.Collections.Generic;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ExchangeShop.Presentation.ViewModel
{
    public record FragmentTradeShopTopViewModel(
        IReadOnlyList<ItemIconViewModel> ItemIconViewModels);
}
