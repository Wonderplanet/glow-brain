using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public interface IArtworkAcquisitionRouteDelegate
    {
        void OnViewDidLoad();
        void OnSelectFragmentDropQuest(EncyclopediaArtworkFragmentListCellViewModel viewModel);
        void OnBackButtonTapped();
    }
}
