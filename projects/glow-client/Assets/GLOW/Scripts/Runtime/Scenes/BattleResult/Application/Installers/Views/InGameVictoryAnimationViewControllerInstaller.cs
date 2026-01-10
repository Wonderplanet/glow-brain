using GLOW.Scenes.BattleResult.Presentation.Presenters;
using GLOW.Scenes.BattleResult.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.BattleResult.Application.Installers.Views
{
    public class InGameVictoryAnimationViewControllerInstaller : Installer
    {
        [Inject] VictoryAnimationViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<VictoryAnimationViewController>();
            Container.BindInterfacesTo<VictoryAnimationPresenter>().AsCached();
        }
    }
}
