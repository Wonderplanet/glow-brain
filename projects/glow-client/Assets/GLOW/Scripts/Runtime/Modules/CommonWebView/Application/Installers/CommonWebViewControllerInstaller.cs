using GLOW.Modules.CommonWebView.Domain.UseCase;
using GLOW.Modules.CommonWebView.Presentation.Presenter;
using GLOW.Modules.CommonWebView.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Modules.CommonWebView.Application.Installers
{
    public class CommonWebViewControllerInstaller : Installer
    {
        [Inject] CommonWebViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<CommonWebViewController>();
            Container.BindInterfacesTo<CommonWebViewPresenter>().AsCached();

            Container.Bind<GetCommonWebUrlUseCase>().AsCached();
            Container.Bind<GetAnnouncementWebUrlUseCase>().AsCached();
            Container.Bind<GetMyIdUseCase>().AsCached();
        }
    }
}
