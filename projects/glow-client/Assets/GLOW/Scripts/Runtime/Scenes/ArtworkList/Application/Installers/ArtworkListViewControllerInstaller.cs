using GLOW.Scenes.ArtworkList.Domain.UseCases;
using GLOW.Scenes.ArtworkList.Presentation.Presenters;
using GLOW.Scenes.ArtworkList.Presentation.Views;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Application.Installers;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkList.Application.Installers
{
    public class ArtworkListViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<ArtworkListViewController>();
            Container.BindInterfacesTo<ArtworkListPresenter>().AsCached();
            Container.Bind<GetArtworkListUseCase>().AsCached();
            Container.Bind<UpdateArtworkSortOrderUseCase>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<ArtworkSortAndFilterDialogViewController, ArtworkSortAndFilterDialogViewControllerInstaller>();
        }
    }
}

