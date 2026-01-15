using GLOW.Scenes.PvpBattleResult.Presentation.Presenter;
using GLOW.Scenes.PvpBattleResult.Presentation.View;
using Zenject;

namespace GLOW.Scenes.PvpBattleResult.Application.View
{
    public class PvpBattleResultRankUpEffectViewControllerInstaller : Installer
    {
        [Inject] PvpBattleResultRankUpEffectViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.Bind<PvpBattleResultRankUpEffectViewController>().AsCached();
            Container.BindInterfacesTo<PvpBattleResultRankUpEffectPresenter>().AsCached();
        }
    }
}