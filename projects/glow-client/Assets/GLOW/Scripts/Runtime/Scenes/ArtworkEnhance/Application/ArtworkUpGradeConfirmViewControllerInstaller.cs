using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Presentation.Presenter;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Application
{
    public class ArtworkUpGradeConfirmViewControllerInstaller : Installer
    {
        [Inject] ArtworkGradeUpConfirmViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<ArtworkGradeUpConfirmViewController>();
            Container.BindInterfacesTo<ArtworkUpGradeConfirmPresenter>().AsCached();

            Container.Bind<ArtworkUpGradeConfirmUseCase>().AsCached();
            Container.Bind<ArtworkGradeUpUseCase>().AsCached();
        }
    }
}
