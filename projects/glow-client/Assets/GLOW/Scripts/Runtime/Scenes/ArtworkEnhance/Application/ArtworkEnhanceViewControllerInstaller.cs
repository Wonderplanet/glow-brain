using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Presentation.Presenter;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.EncyclopediaArtworkDetail.Application.Views;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Application
{
    public class ArtworkEnhanceViewControllerInstaller : Installer
    {
        [Inject] ArtworkEnhanceViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<ArtworkEnhanceViewController>();
            Container.BindInterfacesTo<ArtworkEnhancePresenter>().AsCached();

            Container.BindViewFactoryInfo<EncyclopediaArtworkDetailViewController,
                EncyclopediaArtworkDetailViewControllerInstaller>();

            Container.BindViewFactoryInfo<EncyclopediaArtworkPageViewController,
                EncyclopediaArtworkPageViewControllerInstaller>();

            Container.Bind<ReceiveEncyclopediaFirstCollectionRewardUseCase>().AsCached();
            Container.Bind<ArtworkEnhanceUseCase>().AsCached();
            Container.Bind<InitializeEncyclopediaArtworkCacheUseCase>().AsCached();
        }
    }
}
