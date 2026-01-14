using GLOW.Scenes.Notice.Domain.UseCase;
using GLOW.Scenes.Notice.Presentation.Presenter;
using GLOW.Scenes.Notice.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Notice.Application.Installers
{
    public class NoticeDialogViewControllerInstaller : Installer
    {
        [Inject] NoticeDialogViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<NoticeDialogViewController>();
            Container.BindInterfacesTo<NoticeDialogPresenter>().AsCached();

            Container.BindInterfacesAndSelfTo<NoticeNavigator>().AsCached();
            Container.Bind<SaveNoticeDisplayUseCase>().AsCached();
        }
    }
}
