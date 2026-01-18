using GLOW.Scenes.OutpostArtworkChangeConfirm.Domain.UseCases;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.Presenters;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.OutpostArtworkChangeConfirm.Application.Views
{
    public class OutpostArtworkChangeConfirmViewControllerInstaller : Installer
    {
        [Inject] OutpostArtworkChangeConfirmViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<OutpostArtworkChangeConfirmViewController>();
            Container.BindInterfacesTo<OutpostArtworkChangeConfirmPresenter>().AsCached();
            Container.Bind<GetOutpostArtworkChangeConfirmUseCase>().AsCached();
            Container.Bind<ChangeOutpostArtworkUseCase>().AsCached();

            Container.BindInstance(Argument);
        }
    }
}
