using GLOW.Scenes.PassShopProductDetail.Domain.UseCase;
using GLOW.Scenes.PassShopProductDetail.Presentation.Presenter;
using GLOW.Scenes.PassShopProductDetail.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PassShopProductDetail.Application.View
{
    public class PassShopProductDetailViewControllerInstaller : Installer
    {
        [Inject] PassShopProductDetailViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PassShopProductDetailViewController>();
            Container.BindInterfacesTo<PassShopProductDetailPresenter>().AsCached();
            
            Container.Bind<ShowPassShopProductDetailUseCase>().AsCached();
        }
    }
}