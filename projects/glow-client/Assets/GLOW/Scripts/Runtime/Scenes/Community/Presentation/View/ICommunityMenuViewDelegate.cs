using GLOW.Scenes.Community.Presentation.ViewModel;

namespace GLOW.Scenes.Community.Presentation.View
{
    public interface ICommunityMenuViewDelegate
    {
        void OnViewDidLoad();
        void OnCloseSelected();
        void OnCommunityBannerSelected(CommunityMenuCellViewModel viewModel);
    }
}