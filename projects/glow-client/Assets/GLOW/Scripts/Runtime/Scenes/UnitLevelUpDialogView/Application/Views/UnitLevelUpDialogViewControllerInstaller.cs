using GLOW.Core.Data.Services;
using GLOW.Scenes.UnitLevelUpDialogView.Domain.UseCases;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Presenters;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.UnitLevelUpDialogView.Application.Views
{
    public class UnitLevelUpDialogViewControllerInstaller : Installer
    {
        [Inject] UnitLevelUpDialogViewController.Argument Args { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitLevelUpDialogViewController>();
            Container.BindInterfacesTo<UnitLevelUpDialogPresenter>().AsCached();
            Container.Bind<GetUnitLevelUpDialogModelUseCase>().AsCached();
            Container.Bind<ExecuteUnitLevelUpUseCase>().AsCached();
            Container.BindInstance(Args).AsCached();

            Container.BindInterfacesTo<UnitService>().AsCached();
        }
    }
}
