using GLOW.Scenes.StaminaRecover.Domain;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Application
{
    public class StaminaDiamondRecoverConfirmViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<StaminaDiamondRecoverConfirmViewController>();
            Container.BindInterfacesAndSelfTo<StaminaDiamondRecoverConfirmPresenter>().AsCached();

            Container.Bind<StaminaRecoverExecutionUseCase>().AsCached();
            Container.Bind<StaminaRecoverConfirmUseCase>().AsCached();
            Container.Bind<GetUserMaxStaminaUseCase>().AsCached();
        }
    }
}
