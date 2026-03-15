using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Presentation.Presenter;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Application
{
    public class ArtworkGradeUpAnimViewControllerInstaller : Installer
    {
        [Inject] ArtworkGradeUpAnimViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<ArtworkGradeUpAnimViewController>();
            Container.BindInterfacesTo<ArtworkGradeUpAnimPresenter>().AsCached();

            Container.Bind<ArtworkGradeUpAnimUseCase>().AsCached();
        }
    }
}
