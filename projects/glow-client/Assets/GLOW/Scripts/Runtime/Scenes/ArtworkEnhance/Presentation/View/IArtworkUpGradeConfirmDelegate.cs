using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public interface IArtworkUpGradeConfirmDelegate
    {
        void OnViewDidLoad();
        void OnItemIconTapped(PlayerResourceIconViewModel iconViewModel);
        void OnInfoButtonTapped();
        void OnConfirmButtonTapped();
        void OnBackButtonTapped();
    }
}
