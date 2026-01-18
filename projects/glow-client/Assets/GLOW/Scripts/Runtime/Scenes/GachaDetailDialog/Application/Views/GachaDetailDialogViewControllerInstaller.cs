using GLOW.Core.Data.Services;
using GLOW.Scenes.GachaRatio.Presentation.Presenters;
using GLOW.Scenes.GachaDetailDialog.Presentation.Presenters;
using GLOW.Scenes.GachaDetailDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Application.Views
{
    internal sealed  class GachaDetailDialogViewControllerInstaller : Installer
    {
        [Inject] GachaDetailDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInterfacesTo<GachaService>().AsCached();
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<GachaDetailDialogViewController>();
            Container.BindInterfacesTo<GachaDetailDialogPresenter>().AsCached();
            Container.BindInterfacesTo<GachaRatioPresenter>().AsCached();
            Container.BindInterfacesTo<GachaDetailContentWireFrame>().AsCached();
        }
    }
}
