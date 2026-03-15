using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Presentation.Presenter;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases;
using GLOW.Scenes.OutpostEnhance.Domain.UseCases;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Application
{
    public class ArtworkAcquisitionRouteViewControllerInstaller : Installer
    {
        [Inject] ArtworkAcquisitionRouteViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<ArtworkAcquisitionRouteViewController>();
            Container.BindInterfacesTo<ArtworkAcquisitionRoutePresenter>().AsCached();

            Container.Bind<SetArtworkFragmentDropQuestUseCase>().AsCached();
            Container.Bind<ApplyUpdatedOutpostArtworkUseCase>().AsCached();
            Container.Bind<ArtworkAcquisitionRouteUseCase>().AsCached();
        }
    }
}
