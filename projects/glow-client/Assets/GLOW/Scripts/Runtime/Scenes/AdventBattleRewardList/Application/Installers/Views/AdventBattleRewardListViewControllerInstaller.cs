using GLOW.Scenes.AdventBattleRewardList.Domain.Factory;
using GLOW.Scenes.AdventBattleRewardList.Domain.UseCase;
using GLOW.Scenes.AdventBattleRewardList.Presentation.Presenter;
using GLOW.Scenes.AdventBattleRewardList.Presentation.View;
using Zenject;

namespace GLOW.Scenes.AdventBattleRewardList.Application.Installers.Views
{
    public class AdventBattleRewardListViewControllerInstaller : Installer
    {
        [Inject] AdventBattleRewardListViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.Bind<AdventBattleRewardListViewController>().AsCached();
            Container.BindInterfacesTo<AdventBattleRewardListPresenter>().AsCached();

            Container.BindInterfacesTo<AdventBattleRewardModelFactory>().AsCached();
            Container.Bind<ShowAdventBattleRewardListUseCase>().AsCached();
            
        }
    }
}