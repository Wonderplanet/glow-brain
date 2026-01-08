using GLOW.Scenes.ArtworkFragment.Presentation.Translator;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Presenters
{
    public class EncyclopediaArtworkPagePresenter : IEncyclopediaArtworkPageViewDelegate
    {
        [Inject] EncyclopediaArtworkPageViewController ViewController { get; }
        [Inject] EncyclopediaArtworkPageViewController.Argument Argument { get; }
        [Inject] GetEncyclopediaArtworkPanelUseCase GetEncyclopediaArtworkPanelUseCase { get; }

        void IEncyclopediaArtworkPageViewDelegate.OnViewWillAppear()
        {
            var model = GetEncyclopediaArtworkPanelUseCase.GetArtworkPanel(Argument.MstArtworkId);
            var artworkViewModel = ArtworkPanelViewModelTranslator.ToArtworkFragmentPanelViewModel(model.Artwork);
            ViewController.Setup(artworkViewModel, model.IsArtworkUnlock);
        }
    }
}
