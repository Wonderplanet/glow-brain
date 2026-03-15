using GLOW.Core.Data.Services;
using GLOW.Scenes.StepupGachaRatioDialog.Presentation.Presenters;
using GLOW.Scenes.StepupGachaRatioDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.StepupGachaRatioDialog.Application.Installers
{
    public class StepupGachaRatioDialogViewControllerInstaller : Installer
    {
        [Inject] StepupGachaRatioDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInterfacesTo<GachaService>().AsCached();
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<StepupGachaRatioDialogViewController>();
            Container.BindInterfacesTo<StepupGachaRatioDialogPresenter>().AsCached();
        }
    }
}

