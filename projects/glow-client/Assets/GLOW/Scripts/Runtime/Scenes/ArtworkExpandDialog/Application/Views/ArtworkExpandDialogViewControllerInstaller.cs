using GLOW.Scenes.ArtworkExpandDialog.Domain.Evaluator;
using GLOW.Scenes.ArtworkExpandDialog.Domain.UseCases;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.Presenters;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ArtworkExpandDialog.Application.Views
{
    public class ArtworkExpandDialogViewControllerInstaller : Installer
    {
        [Inject] ArtworkExpandDialogViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<ArtworkExpandDialogViewController>();
            Container.BindInterfacesTo<ArtworkExpandDialogPresenter>().AsCached();
            Container.Bind<GetArtworkExpandUseCase>().AsCached();
            Container.Bind<HasArtworkEvaluator>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
