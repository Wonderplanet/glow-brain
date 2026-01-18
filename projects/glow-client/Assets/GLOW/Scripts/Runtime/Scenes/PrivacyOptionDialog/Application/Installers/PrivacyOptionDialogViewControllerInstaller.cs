using GLOW.Scenes.PrivacyOptionDialog.Domain.UseCases;
using GLOW.Scenes.PrivacyOptionDialog.Presentation.Presenters;
using GLOW.Scenes.PrivacyOptionDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PrivacyOptionDialog.Application.Installers
{
    public class PrivacyOptionDialogViewControllerInstaller : Installer
    {
        [Inject] PrivacyOptionDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PrivacyOptionDialogViewController>();
            Container.BindInterfacesTo<PrivacyOptionDialogPresenter>().AsCached();
        }
    }
}
