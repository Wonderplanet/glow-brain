using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Services;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.ItemBox.Domain.Evaluator;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.StaminaRecover.Domain;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaTrade;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Application
{
    public class StaminaTradeViewControllerInstaller : Installer
    {
        [Inject] StaminaTradeViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<StaminaTradeViewController>();
            Container.BindInterfacesTo<StaminaTradePresenter>().AsCached();

            Container.Bind<ItemApi>().AsCached();
            Container.BindInterfacesTo<ItemService>().AsCached();

            Container.Bind<GetUserMaxStaminaUseCase>().AsCached();
            Container.Bind<GetStaminaUseCase>().AsCached();
            Container.Bind<CreateStaminaTradeUseCase>().AsCached();
            Container.Bind<ActiveItemUseCase>().AsCached();
            Container.Bind<ConsumeItemUseCase>().AsCached();

            Container.Bind<ActiveItemWireFrame>().AsCached();
            Container.Bind<StaminaTradeConfirmWireFrame>().AsCached();
        }
    }
}
