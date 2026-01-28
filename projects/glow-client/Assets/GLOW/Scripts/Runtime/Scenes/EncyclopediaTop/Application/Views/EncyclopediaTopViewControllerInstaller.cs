using GLOW.Scenes.EncyclopediaReward.Application.Views;
using GLOW.Scenes.EncyclopediaReward.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Application.Views;
using GLOW.Scenes.EncyclopediaSeries.Presentation.Views;
using GLOW.Scenes.EncyclopediaTop.Domain.UseCases;
using GLOW.Scenes.EncyclopediaTop.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaTop.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaTop.Application.Views
{
    public class EncyclopediaTopViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EncyclopediaTopViewController>();
            Container.BindInterfacesTo<EncyclopediaTopPresenter>().AsCached();
            Container.Bind<GetEncyclopediaTopUseCase>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<EncyclopediaSeriesViewController, EncyclopediaSeriesViewControllerInstaller>();
            Container.BindViewFactoryInfo<EncyclopediaRewardViewController, EncyclopediaRewardViewControllerInstaller>();
        }
    }
}
