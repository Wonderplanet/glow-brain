using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ItemDetail.Application.Views
{
    internal sealed  class ItemDetailViewControllerInstaller : Installer
    {
        [Inject] ItemDetailViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<ItemDetailViewController>();
            Container.BindInterfacesTo<ItemDetailPresenter>().AsCached();
            Container.Bind<ItemDetailTransitionWireFrame>().AsCached();
        }
    }
}
