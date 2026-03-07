using GLOW.Scenes.ArtworkFormation.Domain.UseCases;
using GLOW.Scenes.ArtworkFormation.Presentation.Presenters;
using GLOW.Scenes.ArtworkFormation.Presentation.Views;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Application.Installers;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkFormation.Application.Installers
{
    public class ArtworkFormationViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<ArtworkFormationViewController>();
            Container.BindInterfacesTo<ArtworkFormationPresenter>().AsCached();
            Container.Bind<ArtworkFormationUseCase>().AsCached();
            Container.Bind<UpdateArtworkSortOrderUseCase>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<ArtworkSortAndFilterDialogViewController, ArtworkSortAndFilterDialogViewControllerInstaller>();
        }
    }
}
