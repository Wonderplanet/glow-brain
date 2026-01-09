using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemBox.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ItemBox.Application.Views
{
    internal sealed class ItemBoxViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.Bind<GetItemBoxItemListUseCase>().AsCached();
            Container.Bind<GetItemBoxItemUseCase>().AsCached();
            Container.Bind<GetCommonReceiveItemUseCase>().AsCached();

            Container.BindViewWithKernal<ItemBoxViewController>();
            Container.BindInterfacesTo<ItemBoxPresenter>().AsCached();
        }
    }
}
