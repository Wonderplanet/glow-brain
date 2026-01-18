using GLOW.Scenes.BoxGachaConfirm.Domain.UseCase;
using GLOW.Scenes.BoxGachaConfirm.Presentation.Presenter;
using GLOW.Scenes.BoxGachaConfirm.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.BoxGachaConfirm.Application.Installers
{
    public class BoxGachaConfirmDialogViewControllerInstaller : Installer
    {
        [Inject] BoxGachaConfirmDialogViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<BoxGachaConfirmDialogViewController>();
            Container.BindInterfacesTo<BoxGachaConfirmDialogPresenter>().AsCached();
            
            Container.Bind<ShowBoxGachaConfirmUseCase>().AsCached();
        }
    }
}