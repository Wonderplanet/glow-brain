using GLOW.Scenes.BoxGacha.Domain.UseCase;
using GLOW.Scenes.BoxGacha.Presentation.Presenter;
using GLOW.Scenes.BoxGacha.Presentation.View;
using GLOW.Scenes.BoxGacha.Presentation.WireFrame;
using GLOW.Scenes.BoxGachaConfirm.Application.Installers;
using GLOW.Scenes.BoxGachaConfirm.Presentation.View;
using GLOW.Scenes.BoxGachaLineupDialog.Application.Installers;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.View;
using GLOW.Scenes.BoxGachaResult.Application.Installers;
using GLOW.Scenes.BoxGachaResult.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Application.Installers
{
    public class BoxGachaTopViewControllerInstaller : Installer
    {
        [Inject] BoxGachaTopViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<BoxGachaTopViewController>();
            Container.BindInterfacesTo<BoxGachaTopPresenter>().AsCached();
            
            Container.Bind<DrawBoxGachaUseCase>().AsCached();
            Container.Bind<ResetBoxGachaUseCase>().AsCached();
            Container.Bind<BoxGachaExceptionWireFrame>().AsCached();
            
            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<BoxGachaLineupDialogViewController, BoxGachaLineupDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<BoxGachaConfirmDialogViewController, BoxGachaConfirmDialogViewControllerInstaller>();
        }
    }
}