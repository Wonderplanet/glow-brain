using GLOW.Scenes.StaminaRecover.Domain;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Application
{
    public class StaminaRecoverViewInstaller : Installer
    {
        [Inject] StaminaRecoverSelectViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<StaminaRecoverSelectViewController>();
            Container.BindInterfacesTo<StaminaRecoverSelectPresenter>().AsCached();

            Container.Bind<StaminaRecoverSelectUseCase>().AsCached();

            Container.BindFactory<StaminaDiamondRecoverConfirmViewController, PlaceholderFactory<StaminaDiamondRecoverConfirmViewController>>();
            Container.BindInterfacesTo<StaminaDiamondRecoverConfirmPresenter>().AsCached();
            Container.Bind<StaminaRecoverConfirmUseCase>().AsCached();
            Container.Bind<StaminaRecoverExecutionUseCase>().AsCached();
            Container.Bind<CalculateReceivableStaminaTimeUseCase>().AsCached();
        }
    }
}
