using GLOW.Scenes.AgreementDialog.Presentation.Presenters;
using GLOW.Scenes.AgreementDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AgreementDialog.Application.Installers
{
    public class AgreementDialogViewControllerInstaller : Installer
    {
        [Inject] AgreementDialogViewController.Argument Argument { get; set; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<AgreementDialogViewController>();
            Container.BindInterfacesTo<AgreementDialogPresenter>().AsCached();
        }
    }
}
