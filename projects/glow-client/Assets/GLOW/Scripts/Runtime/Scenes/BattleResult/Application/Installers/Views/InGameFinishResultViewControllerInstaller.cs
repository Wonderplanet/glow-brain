using GLOW.Scenes.BattleResult.Presentation.Presenters;
using GLOW.Scenes.BattleResult.Presentation.Views.FinishResult;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.BattleResult.Application.Installers.Views
{
    public class InGameFinishResultViewControllerInstaller : Installer
    {
        [Inject] FinishResultViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<FinishResultViewController>();
            Container.BindInterfacesTo<FinishResultPresenter>().AsCached();
        }
    }
}
