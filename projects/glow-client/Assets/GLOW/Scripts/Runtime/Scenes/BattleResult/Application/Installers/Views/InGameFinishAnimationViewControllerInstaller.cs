using GLOW.Scenes.BattleResult.Presentation.Presenters;
using GLOW.Scenes.BattleResult.Presentation.Views.FinishResult;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.BattleResult.Application.Installers.Views
{
    public class InGameFinishAnimationViewControllerInstaller : Installer
    {
        [Inject] FinishAnimationViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<FinishAnimationViewController>();
            Container.BindInterfacesTo<FinishAnimationPresenter>().AsCached();
        }
    }
}
