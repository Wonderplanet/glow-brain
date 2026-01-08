using GLOW.Scenes.TutorialGachaReDrawDialog.Presentation.Presenters;
using GLOW.Scenes.TutorialGachaReDrawDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.TutorialGachaReDrawDialog.Application.Installers
{
    public class TutorialGachaReDrawDialogViewControllerInstaller : Installer
    {
        [Inject] TutorialGachaReDrawDialogViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<TutorialGachaReDrawDialogViewController>();
            Container.BindInterfacesTo<TutorialGachaReDrawDialogPresenter>().AsCached();
        }
    }
}