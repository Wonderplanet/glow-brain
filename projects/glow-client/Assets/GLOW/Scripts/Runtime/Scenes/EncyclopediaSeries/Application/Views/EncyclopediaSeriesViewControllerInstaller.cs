using GLOW.Scenes.EncyclopediaArtworkDetail.Application.Views;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaEmblemDetail.Application;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaEnemyDetail.Application.Views;
using GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.EncyclopediaSeries.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaSeries.Presentation.Views;
using GLOW.Scenes.EncyclopediaUnitDetail.Application.Views;
using GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaSeries.Application.Views
{
    public class EncyclopediaSeriesViewControllerInstaller : Installer
    {
        [Inject] EncyclopediaSeriesViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EncyclopediaSeriesViewController>();
            Container.BindInterfacesTo<EncyclopediaSeriesPresenter>().AsCached();
            Container.Bind<GetEncyclopediaSeriesUnitListUseCase>().AsCached();
            Container.Bind<GetEncyclopediaSeriesInfoUseCase>().AsCached();
            Container.Bind<GetEncyclopediaSeriesCollectionListUseCase>().AsCached();
            Container.Bind<GetEncyclopediaSeriesUrlUseCase>().AsCached();
            Container.BindInstance(Argument);

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<EncyclopediaUnitDetailViewController, EncyclopediaUnitDetailVIewControllerInstaller>();
            Container.BindViewFactoryInfo<EncyclopediaEnemyDetailViewController, EncyclopediaEnemyDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<EncyclopediaEmblemDetailViewController, EncyclopediaEmblemDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<EncyclopediaArtworkDetailViewController, EncyclopediaArtworkDetailViewControllerInstaller>();
        }
    }
}
