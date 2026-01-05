using GLOW.Scenes.InGame.Presentation.Presenters;
using GLOW.Scenes.InGame.Presentation.Views.DefeatDialog;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.InGame.Application.Installers
{
    public class DefeatDialogViewControllerInstaller : Installer
    {
        [Inject] DefeatDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<DefeatDialogViewController>();
            Container.BindInterfacesTo<DefeatDialogPresenter>().AsCached();
        }
    }
}
