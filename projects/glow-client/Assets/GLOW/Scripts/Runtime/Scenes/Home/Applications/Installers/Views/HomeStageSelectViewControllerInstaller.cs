using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Home.Applications.Installers.Views
{
    internal sealed class HomeStageSelectViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeStageSelectViewController>();
            Container.BindInterfacesTo<HomeStageSelectPresenter>().AsCached();
        }
    }
}
