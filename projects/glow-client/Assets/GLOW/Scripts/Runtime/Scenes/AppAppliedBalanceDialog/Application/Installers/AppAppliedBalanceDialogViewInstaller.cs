using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.AppAppliedBalanceDialog.Presentation;
using GLOW.Scenes.AppAppliedBalanceDialog.Domain;

namespace GLOW.Scenes.AppAppliedBalanceDialog.Application
{
    public class AppAppliedBalanceDialogViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<AppAppliedBalanceDialogViewController>();
            Container.BindInterfacesTo<AppAppliedBalanceDialogPresenter>().AsCached();

            Container.Bind<GetAppAppliedBalanceUseCase>().AsCached();
            Container.Bind<AppAppliedBalanceViewModelTranslator>().AsCached();
        }
    }
}
