using GLOW.Scenes.Community.Domain.UseCase;
using GLOW.Scenes.Community.Presentation.Presenter;
using GLOW.Scenes.Community.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Community.Application.Installers.Views
{
    public class CommunityMenuViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<CommunityMenuViewController>();
            Container.BindInterfacesTo<CommunityMenuPresenter>().AsCached();
            
            Container.Bind<CommunityListUseCase>().AsCached();
        }
    }
}