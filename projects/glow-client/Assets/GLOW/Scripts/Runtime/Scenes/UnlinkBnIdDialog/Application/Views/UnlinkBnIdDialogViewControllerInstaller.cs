using GLOW.Scenes.UnlinkBnIdDialog.Domain.UseCases;
using GLOW.Scenes.UnlinkBnIdDialog.Presentation.Presenters;
using GLOW.Scenes.UnlinkBnIdDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.UnlinkBnIdDialog.Application.Views
{
    public class UnlinkBnIdDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnlinkBnIdDialogViewController>();
            Container.BindInterfacesTo<UnlinkBnIdDialogPresenter>().AsCached();
            Container.Bind<UnlinkBnIdUseCase>().AsCached();
        }
    }
}
