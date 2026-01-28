using GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.Presenters;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.TitleResultLinkBnIdDialog.Application.Installers
{
    public class TitleBnIdLinkageResultDialogViewControllerInstaller : Installer
    {
        [Inject] TitleBnIdLinkageResultDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<TitleBnIdLinkageResultDialogViewController>();
            Container.BindInterfacesTo<TitleBnIdLinkageBnIdLinkageResultDialogPresenter>().AsCached();
        }
    }
}
