using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public interface IArtworkGradeContentsDelegate
    {
        void OnViewDidLoad();
        void OnItemIconTapped(PlayerResourceIconViewModel iconViewModel);
        void OnCloseButtonTapped();
    }
}
