using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView
{
    public interface IHomeStageInfoViewDelegate
    {
        void OnViewDidLoad(HomeStageInfoViewModel viewModel);
        void OnViewDidUnload();
        void OnClose();
        void OnTappedPlayerResourceIcon(PlayerResourceIconViewModel viewModel);
    }
}
