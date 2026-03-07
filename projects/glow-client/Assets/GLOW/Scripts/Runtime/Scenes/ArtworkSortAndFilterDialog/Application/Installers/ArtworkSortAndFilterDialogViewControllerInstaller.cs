using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Presenters;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Application.Installers
{
    public class ArtworkSortAndFilterDialogViewControllerInstaller : Installer
    {
        [Inject] ArtworkSortAndFilterDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<ArtworkSortAndFilterDialogViewController>();
            Container.BindInterfacesTo<ArtworkSortAndFilterDialogPresenter>().AsCached();

            Container.Bind<HasAnyMatchingFilterArtworkUseCase>().AsCached();
            Container.Bind<UpdateArtworkSortFilterCacheUseCase>().AsCached();
        }
    }
}
