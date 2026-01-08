using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Modules.CommonReceiveView.Application.Installers.Views
{
    public class AsyncCommonReceiveViewControllerInstaller : Installer
    {
        [Inject] AsyncCommonReceiveViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.Bind<GetCommonReceiveItemUseCase>().AsCached();
            Container.BindViewWithKernal<AsyncCommonReceiveViewController>();
            Container.BindInterfacesTo<AsyncCommonReceivePresenter>().AsCached();
        }
    }
}