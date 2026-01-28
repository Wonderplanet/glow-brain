using GLOW.Scenes.InGame.Presentation.Presenters;
using GLOW.Scenes.InGame.Presentation.Views.SpecialUnitSummonConfirmationDialog;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.InGame.Application.Installers
{
    public class SpecialUnitSummonConfirmationDialogViewControllerInstaller : Installer
    {
        [Inject] SpecialUnitSummonConfirmationDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<SpecialUnitSummonConfirmationDialogViewController>();
            Container.BindInterfacesTo<SpecialUnitSummonConfirmationDialogPresenter>().AsCached();
        }
    }
}
