using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ItemDetail.Presentation.Views;

namespace GLOW.Scenes.ItemDetail.Presentation.ViewModels
{
    public record ItemDetailWithTransitViewModel(
        PlayerResourceDetailViewModel PlayerResourceDetailViewModel,
        ItemDetailAmountViewModel ItemDetailAmountViewModel,
        ItemDetailAvailableLocationViewModel ItemDetailAvailableLocationViewModel);
}