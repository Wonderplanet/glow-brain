using GLOW.Modules.CommonWebView.Domain.UseCase;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Views.HomeMainBanner;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Home.Applications.Installers.Views
{
    public class HomeMainBannerItemViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeMainBannerItemViewController>();
            Container.BindInterfacesTo<HomeMainBannerItemPresenter>().AsCached();
            Container.Bind<GetMyIdUseCase>().AsCached();
        }
    }
}
