using GLOW.Core.Presentation.Wireframe;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.StaminaRecover.Domain;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Application
{
    public class StaminaRecoverySelectViewControllerInstaller : Zenject.Installer
    {
        [Inject] StaminaRecoverySelectViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<StaminaRecoverySelectViewController>();
            Container.BindInterfacesTo<StaminaRecoverySelectPresenter>().AsCached();

            Container.Bind<StaminaRecoverExecutionUseCase>().AsCached();
            Container.Bind<GetStaminaRecoveryItemUseCase>().AsCached();
            Container.Bind<GetHeldAdSkipPassInfoUseCase>().AsCached();
            Container.Bind<GetUserMaxStaminaUseCase>().AsCached();

            Container.Bind<InAppAdvertisingWireframe>().AsCached();
        }
    }
}
