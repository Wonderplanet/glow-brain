using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.GachaCostItemDetailView.Domain.Models;
using GLOW.Scenes.GachaCostItemDetailView.Presentation.ViewModels;
using GLOW.Scenes.ItemDetail.Presentation.Views;

namespace GLOW.Scenes.GachaCostItemDetailView.Presentation.Translator
{
    public class GachaCostItemDetailTranslator
    {
        public static GachaCostItemDetailViewModel Translate(
            GachaCostItemDetailUseCaseModel useCaseModel,
            ShowTransitAreaFlag showTransitAreaFlag)
        {
            return new GachaCostItemDetailViewModel(
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(useCaseModel.PlayerResourceModel),
                useCaseModel.PlayerResourceModel.Name,
                useCaseModel.PlayerResourceModel.Description,
                useCaseModel.TransitionButtonGrayOutFlag,
                showTransitAreaFlag);
        }
    }
}