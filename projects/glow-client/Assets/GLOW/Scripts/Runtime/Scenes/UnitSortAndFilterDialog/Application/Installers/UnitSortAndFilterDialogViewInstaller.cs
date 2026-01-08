using System.ComponentModel;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Presenters;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Application.Installers
{
    public class UnitSortAndFilterDialogViewInstaller : Installer
    {
        [Inject] UnitSortAndFilterDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<UnitSortAndFilterDialogViewController>();
            Container.BindInterfacesTo<UnitSortAndFilterDialogPresenter>().AsCached();

            Container.Bind<UpdateSortFilterCacheUseCase>().AsCached();
            Container.Bind<HasAnyMatchingFilterUnitUseCase>().AsCached();
        }
    }
}
