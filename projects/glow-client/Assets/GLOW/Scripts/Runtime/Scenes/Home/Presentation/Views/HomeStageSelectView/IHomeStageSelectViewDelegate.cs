using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public interface IHomeStageSelectViewDelegate
    {
        void OnViewDidLoad();
        void OnStageSelected(HomeMainStageViewModel stageViewModel);
    }
}
