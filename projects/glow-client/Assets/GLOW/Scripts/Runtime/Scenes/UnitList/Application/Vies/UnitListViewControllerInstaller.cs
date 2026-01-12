using GLOW.Core.Domain.Helper;
using GLOW.Scenes.UnitList.Domain.UseCases;
using GLOW.Scenes.UnitList.Presentation.Presenters;
using GLOW.Scenes.UnitList.Presentation.Views;
using GLOW.Scenes.UnitSortAndFilterDialog.Application.Installers;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.UnitList.Application.Vies
{
    public class UnitListViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitListViewController>();
            Container.BindInterfacesTo<UnitListPresenter>().AsCached();

            Container.Bind<GetUnitListUseCase>().AsCached();
            Container.Bind<SetupUnitListConditionalFilterUseCase>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<UnitSortAndFilterDialogViewController, UnitSortAndFilterDialogViewInstaller>();
        }
    }
}
