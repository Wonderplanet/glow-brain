using GLOW.Core.Data.Services;
using GLOW.Scenes.ItemBox.Domain.Factory;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemBox.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ItemBox.Application.Views
{
    public class FragmentBoxTradeViewControllerInstaller : Installer
    {
        [Inject] FragmentBoxTradeViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            
            Container.BindViewWithKernal<FragmentBoxTradeViewController>();
            Container.BindInterfacesTo<FragmentBoxTradePresenter>().AsCached();

            Container.BindInterfacesTo<ItemService>().AsCached();
            Container.BindInterfacesTo<FragmentBoxTradeModelFactory>().AsCached();
            
            Container.Bind<ShowFragmentBoxTradeUseCase>().AsCached();
            Container.Bind<CheckFragmentBoxTradableUseCase>().AsCached();
            Container.Bind<ConsumeItemUseCase>().AsCached();
        }
    }
}