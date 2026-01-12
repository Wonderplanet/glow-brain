using GLOW.Scenes.BattleResult.Application.Installers.Views;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.GachaConfirm.Domain.UseCases;
using GLOW.Scenes.GachaConfirm.Presentation.Presenters;
using GLOW.Scenes.GachaConfirm.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.GachaConfirm.Application.Views
{
    public class GachaConfirmDialogViewControllerInstaller : Installer
    {
        [Inject] GachaConfirmDialogViewController.Argument Argument { get; set; }
        public override void InstallBindings()
        {
            Container.Bind<GachaConfirmDialogUseCase>().AsCached();
            Container.BindViewWithKernal<GachaConfirmDialogViewController>();
            Container.BindInstance(Argument);
            Container.BindInterfacesTo<GachaConfirmDialogPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<DiamondPurchaseViewController, DiamondPurchaseViewControllerInstaller>();
        }
    }
}
