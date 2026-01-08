using System.Collections.Generic;

namespace GLOW.Scenes.ExchangeShop.Presentation.ViewModel
{
    public record ExchangeContentTopViewModel(
        IReadOnlyList<ExchangeContentCellViewModel> CellViewModels);
}
