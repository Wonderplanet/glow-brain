using GLOW.Scenes.BoxGachaLineupDialog.Domain.UseCase;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.Presenter;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.BoxGachaLineupDialog.Application.Installers
{
    public class BoxGachaLineupDialogViewControllerInstaller : Installer
    {
        [Inject] BoxGachaLineupDialogViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<BoxGachaLineupDialogViewController>();
            Container.BindInterfacesTo<BoxGachaLineupDialogPresenter>().AsCached();
            Container.Bind<ShowBoxGachaLineupUseCase>().AsCached();
        }
    }
}