using GLOW.Scenes.Notice.Domain.UseCase;
using GLOW.Scenes.Notice.Presentation.Presenter;
using GLOW.Scenes.Notice.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Notice.Application.Installers
{
    public class NoticeSimpleBannerViewControllerInstaller : Installer
    {
        [Inject] NoticeSimpleBannerViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<NoticeSimpleBannerViewController>();
            Container.BindInterfacesTo<NoticeSimpleBannerPresenter>().AsCached();

            Container.BindInterfacesAndSelfTo<NoticeNavigator>().AsCached();
            Container.Bind<SaveNoticeDisplayUseCase>().AsCached();
        }
    }
}
