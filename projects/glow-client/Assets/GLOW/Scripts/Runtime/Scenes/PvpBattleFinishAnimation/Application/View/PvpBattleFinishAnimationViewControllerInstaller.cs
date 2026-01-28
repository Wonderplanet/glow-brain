using GLOW.Scenes.PvpBattleFinishAnimation.Presentation.Presenter;
using GLOW.Scenes.PvpBattleFinishAnimation.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PvpBattleFinishAnimation.Application.View
{
    public class PvpBattleFinishAnimationViewControllerInstaller : Installer
    {
        [Inject] PvpBattleFinishAnimationViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PvpBattleFinishAnimationViewController>();
            Container.BindInterfacesTo<PvpBattleFinishAnimationPresenter>().AsCached();
        }
    }
}