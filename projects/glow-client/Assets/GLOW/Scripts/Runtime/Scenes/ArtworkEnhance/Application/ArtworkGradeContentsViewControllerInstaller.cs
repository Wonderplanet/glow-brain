using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Presentation.Presenter;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Application
{
    public class ArtworkGradeContentsViewControllerInstaller : Installer
    {
        [Inject] ArtworkGradeContentsViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<ArtworkGradeContentsViewController>();
            Container.BindInterfacesTo<ArtworkGradeContentsPresenter>().AsCached();

            Container.Bind<ArtworkGradeContentsUseCase>().AsCached();
        }
    }
}
