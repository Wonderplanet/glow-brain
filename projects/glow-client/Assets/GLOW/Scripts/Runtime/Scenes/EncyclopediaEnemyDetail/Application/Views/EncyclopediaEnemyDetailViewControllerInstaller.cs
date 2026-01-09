using GLOW.Modules.UnitAvatarPageView.Application.Views;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.EncyclopediaEnemyDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEnemyDetail.Application.Views
{
    public class EncyclopediaEnemyDetailViewControllerInstaller : Installer
    {
        [Inject] EncyclopediaEnemyDetailViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EncyclopediaEnemyDetailViewController>();
            Container.BindInterfacesTo<EncyclopediaEnemyDetailPresenter>().AsCached();
            Container.Bind<GetEncyclopediaEnemyDetailUseCase>().AsCached();
            Container.Bind<ReceiveEncyclopediaFirstCollectionRewardUseCase>().AsCached();

            Container.BindInstance(Argument).AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<UnitAvatarPageViewController, EnemyAvatarPageViewControllerInstaller>();
        }
    }
}
