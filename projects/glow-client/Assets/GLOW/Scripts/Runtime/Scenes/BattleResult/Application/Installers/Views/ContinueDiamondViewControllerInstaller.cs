using GLOW.Modules.CommonWebView.Application.Installers;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.CommonWebView.Presentation.View;
using GLOW.Scenes.BattleResult.Presentation.Presenters;
using GLOW.Scenes.BattleResult.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Application.Installers.Views
{
    public class ContinueDiamondViewControllerInstaller : Installer
    {
        [Inject] ContinueDiamondViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();

            Container.BindViewWithKernal<ContinueDiamondViewController>();
            Container.BindInterfacesTo<ContinueDiamondPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindInterfacesTo<CommonWebViewControl>().AsCached();
            Container.BindViewFactoryInfo<CommonWebViewController, CommonWebViewControllerInstaller>();
        }
    }
}
