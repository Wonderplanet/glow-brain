using GLOW.Scenes.TitleLinkBnIdDialog.Domain.UseCases;
using GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Presenters;
using GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Domain.UseCases;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.TitleLinkBnIdDialog.Application.Views
{
    public class TitleLinkBnIdDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<TitleLinkBnIdDialogViewController>();
            Container.BindInterfacesTo<TitleLinkBnIdDialogPresenter>().AsCached();
            Container.Bind<LinkBnIdConfirmUseCase>().AsCached();
            Container.Bind<LinkBnIdForTitleUseCase>().AsCached();
        }
    }
}
