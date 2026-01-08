using GLOW.Scenes.AdventBattle.Domain.UseCase;
using GLOW.Scenes.AdventBattle.Presentation.Presenter;
using GLOW.Scenes.AdventBattle.Presentation.View.AdventBattleInfo;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Application
{
    public class AdventBattleInfoViewControllerInstaller : Installer
    {
        [Inject] AdventBattleInfoViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<AdventBattleInfoViewController>();
            Container.BindInterfacesTo<AdventBattleInfoPresenter>().AsCached();

            Container.Bind<AdventBattleInfoUseCase>().AsCached();
            
            Container.BindInterfacesTo<AutoPlayerSequenceModelFactory>().AsCached();
        }
    }
}