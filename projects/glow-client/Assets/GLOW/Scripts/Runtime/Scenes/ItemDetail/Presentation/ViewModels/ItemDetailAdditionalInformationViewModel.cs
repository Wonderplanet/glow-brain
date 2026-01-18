using GLOW.Scenes.ItemDetail.Presentation.Views;

namespace GLOW.Scenes.ItemDetail.Presentation.ViewModels
{
    public record ItemDetailAdditionalInformationViewModel(
        ItemDetailAmountViewModel ItemDetailAmountViewModel,
        ItemDetailAvailableLocationViewModel ItemDetailAvailableLocationViewModel);
}