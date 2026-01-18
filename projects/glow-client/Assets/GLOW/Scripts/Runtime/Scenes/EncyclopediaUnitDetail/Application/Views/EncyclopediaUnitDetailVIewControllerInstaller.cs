using GLOW.Modules.UnitAvatarPageView.Application.Views;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.EncyclopediaUnitDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Application.Views;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Views;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Components;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaUnitDetail.Application.Views
{
    public class EncyclopediaUnitDetailVIewControllerInstaller : Installer
    {
        [Inject] EncyclopediaUnitDetailViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EncyclopediaUnitDetailViewController>();
            Container.BindInterfacesTo<EncyclopediaUnitDetailPresenter>().AsCached();
            Container.Bind<GetEncyclopediaUnitDetailUseCase>().AsCached();
            Container.Bind<ReceiveEncyclopediaFirstCollectionRewardUseCase>().AsCached();

            Container.BindInstance(Argument).AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<UnitAvatarPageViewController, UnitAvatarPageViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitSpecialAttackPreviewViewController, UnitSpecialAttackPreviewViewControllerInstaller>();

            // 必殺ワザ演出用
            Container.BindInterfacesAndSelfTo<ViewCoordinateConverter>().AsCached();
            Container.Bind<PrefabFactory<KomaSetComponent>>().AsCached();
            Container.BindInterfacesTo<KomaBackgroundLoader>().AsCached();
        }
    }
}
