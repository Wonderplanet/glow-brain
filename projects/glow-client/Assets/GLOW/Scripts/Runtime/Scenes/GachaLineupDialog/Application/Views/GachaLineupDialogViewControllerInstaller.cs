using GLOW.Core.Data.Services;
using GLOW.Scenes.GachaLineupDialog.Presentation.Presenters;
using GLOW.Scenes.GachaLineupDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaLineupDialog.Application.Views
{
    public class GachaLineupDialogViewControllerInstaller : Installer
    {
        [Inject] GachaLineupDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInterfacesTo<GachaService>().AsCached();
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<GachaLineupDialogViewController>();
            Container.BindInterfacesTo<GachaLineupDialogPresenter>().AsCached();
        }
    }
}