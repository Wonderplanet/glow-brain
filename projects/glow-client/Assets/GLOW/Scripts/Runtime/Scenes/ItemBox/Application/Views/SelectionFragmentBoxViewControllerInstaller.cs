using GLOW.Core.Data.Services;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemBox.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ItemBox.Application.Views
{
    public class SelectionFragmentBoxViewControllerInstaller : Installer
    {
        [Inject] SelectionFragmentBoxViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();

            Container.BindInterfacesTo<ItemService>().AsCached();
            Container.Bind<GetFragmentLineupUseCase>().AsCached();
            Container.Bind<ExchangeToSelectedItemUseCase>().AsCached();

            Container.BindViewWithKernal<SelectionFragmentBoxViewController>();
            Container.BindInterfacesTo<SelectionFragmentBoxPresenter>().AsCached();

         }
    }
}
