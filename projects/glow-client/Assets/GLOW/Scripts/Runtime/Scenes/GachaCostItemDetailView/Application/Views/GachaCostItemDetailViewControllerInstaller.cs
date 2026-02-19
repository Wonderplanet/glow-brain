using GLOW.Scenes.GachaCostItemDetailView.Domain.UseCases;
using GLOW.Scenes.GachaCostItemDetailView.Presentation.Presenters;
using GLOW.Scenes.GachaCostItemDetailView.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaCostItemDetailView.Application.Views
{
    internal sealed class GachaCostItemDetailViewControllerInstaller : Installer
    {
        [Inject] GachaCostItemDetailViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<GachaCostItemDetailViewController>();
            Container.BindInterfacesTo<GachaCostItemDetailPresenter>().AsCached();

            Container.Bind<GachaCostItemDetailUseCase>().AsCached();
        }
    }
}