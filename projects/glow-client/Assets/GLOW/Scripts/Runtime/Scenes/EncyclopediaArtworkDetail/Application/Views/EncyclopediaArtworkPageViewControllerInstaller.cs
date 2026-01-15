using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Application.Views
{
    public class EncyclopediaArtworkPageViewControllerInstaller : Installer
    {
        [Inject] EncyclopediaArtworkPageViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EncyclopediaArtworkPageViewController>();
            Container.BindInterfacesTo<EncyclopediaArtworkPagePresenter>().AsCached();
            Container.Bind<GetEncyclopediaArtworkPanelUseCase>().AsCached();

            Container.BindInstance(Argument).AsCached();
        }
    }
}
