using GLOW.Core.Data.Services;
using GLOW.Scenes.GachaDetailDialog.Domain.UseCases;
using GLOW.Scenes.GachaRatio.Domain.UseCases;
using GLOW.Scenes.PackShopGacha.Domain.UseCases;
using GLOW.Scenes.PackShopGacha.Presentation.Presenters;
using GLOW.Scenes.PackShopGacha.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PackShopGacha.Application
{
    public class PackShopGachaViewControllerInstaller : Installer
    {
        [Inject] PackShopGachaViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PackShopGachaViewController>();
            Container.BindInterfacesTo<PackShopGachaPresenter>().AsCached();
            Container.Bind<PackShopGachaUseCase>().AsSingle();
            
            // ガシャ提供割合表示
            Container.Bind<GachaRatioDialogUseCase>().AsCached();
            Container.BindInterfacesTo<GachaService>().AsCached();
            Container.BindInterfacesTo<GachaRatioPageModelFactory>().AsCached();
            Container.BindInterfacesTo<AnnouncementCellUseCaseModelFactory>().AsCached();
            Container.BindInterfacesTo<AnnouncementService>().AsCached();
            
        }
    }
}