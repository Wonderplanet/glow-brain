using GLOW.Core.Data.Services;
using GLOW.Scenes.GachaRatio.Presentation.Views;
using GLOW.Scenes.GachaRatio.Presentation.Presenters;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaRatio.Application.Views
{
    public class GachaRatioViewControllerInstaller : Installer
    {
        [Inject] GachaRatioDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInterfacesTo<GachaService>().AsCached();
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<GachaRatioDialogViewController>();
            Container.BindInterfacesTo<GachaRatioPresenter>().AsCached();
        }
    }
}
