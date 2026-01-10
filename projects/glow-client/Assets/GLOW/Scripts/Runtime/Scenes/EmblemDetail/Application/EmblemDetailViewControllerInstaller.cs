using GLOW.Scenes.EmblemDetail.Presentation.Presenters;
using GLOW.Scenes.EmblemDetail.Presentation.Views;
using GLOW.Scenes.EmblemDetail.Domain.UseCases;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.EmblemDetail.Application
{
    public class EmblemDetailViewControllerInstaller : Installer
    {
        [Inject] EmblemDetailViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EmblemDetailViewController>();
            Container.BindInterfacesTo<EmblemDetailPresenter>().AsCached();
            Container.Bind<GetEmblemDetailUseCase>().AsCached();

            Container.BindInstance(Argument);
        }
    }
}
