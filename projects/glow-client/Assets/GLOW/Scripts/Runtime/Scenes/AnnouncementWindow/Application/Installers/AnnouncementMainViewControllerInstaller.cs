using GLOW.Core.Data.Services;
using GLOW.Scenes.AnnouncementWindow.Domain.Applier;
using GLOW.Scenes.AnnouncementWindow.Domain.UseCase;
using GLOW.Scenes.AnnouncementWindow.Presentation.Presenter;
using GLOW.Scenes.AnnouncementWindow.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Application.Installers
{
    public class AnnouncementMainViewControllerInstaller : Installer
    {
        [Inject] AnnouncementMainViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<AnnouncementMainViewController>();
            Container.BindInstance(Argument).AsCached();
            Container.BindInterfacesTo<AnnouncementMainPresenter>().AsCached();

            Container.Bind<UpdateAnnouncementListUseCase>().AsCached();
            Container.Bind<SaveAnnouncementReadTimeUseCase>().AsCached();
            Container.Bind<GetCachedAnnouncementListUseCase>().AsCached();
            Container.BindInterfacesTo<AnnouncementService>().AsCached();
            Container.BindInterfacesTo<AnnouncementDateTimeApplier>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<AnnouncementContentViewController, AnnouncementContentViewControllerInstaller>();
        }
    }
}
