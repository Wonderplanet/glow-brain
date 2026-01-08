using GLOW.Core.Data.Repositories;
using GLOW.Core.Data.Services;
using GLOW.Scenes.GachaAnim.Application.Views;
using GLOW.Scenes.GachaAnim.Presentation.Views;
using GLOW.Scenes.GachaConfirm.Application.Views;
using GLOW.Scenes.GachaConfirm.Presentation.Views;
using GLOW.Scenes.GachaContent.Application.Views;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaList.Domain.UseCases;
using GLOW.Scenes.GachaList.Presentation.Presenters;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.GachaResult.Application.Views;
using GLOW.Scenes.GachaResult.Presentation.Views;
using GLOW.Scenes.GachaDetailDialog.Domain.UseCases;
using GLOW.Scenes.GachaHistoryDetailDialog.Application.Installers;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Views;
using GLOW.Scenes.GachaHistoryDialog.Application.Installers;
using GLOW.Scenes.GachaHistoryDialog.Domain.UseCases;
using GLOW.Scenes.GachaHistoryDialog.Presentation.Presenters;
using GLOW.Scenes.GachaHistoryDialog.Presentation.Views;
using GLOW.Scenes.GachaLineupDialog.Domain.Factory;
using GLOW.Scenes.GachaLineupDialog.Domain.UseCases;
using GLOW.Scenes.GachaList.Domain.Applier;
using GLOW.Scenes.GachaRatio.Domain.UseCases;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;

namespace GLOW.Scenes.GachaList.Application.Views
{
    public class GachaListViewControllerInstaller : Zenject.Installer
    {
        public override void InstallBindings()
        {
            Container.Bind<GachaListUseCase>().AsCached();

            Container.BindViewWithKernal<GachaListViewController>();
            Container.BindInterfacesTo<GachaListPresenter>().AsCached();
            Container.BindInterfacesTo<GashaPrizeCacheRepository>().AsCached();
            Container.BindInterfacesTo<FestivalGachaBannerImageLoader>().AsCached();

            Container.BindViewFactoryInfo<GachaContentViewController, GachaContentViewControllerInstaller>();
            Container.BindInterfacesTo<GachaService>().AsCached();
            Container.Bind<GachaDrawUseCase>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            // ガシャ確認ダイアログ
            Container.BindViewFactoryInfo<GachaConfirmDialogViewController, GachaConfirmDialogViewControllerInstaller>();

            // ガシャ演出
            Container.BindViewFactoryInfo<GachaAnimViewController, GachaAnimViewControllerInstaller>();
            Container.Bind<GachaAnimationLoadUseCase>().AsCached();
            Container.Bind<GachaAnimationUnloadUseCase>().AsCached();

            // ガシャ結果
            Container.BindViewFactoryInfo<GachaResultViewController, GachaResultViewControllerInstaller>();
            Container.BindInterfacesTo<GachaDrawResultApplier>().AsCached();

            // ガシャ詳細
            Container.Bind<GachaRatioDialogUseCase>().AsCached();
            Container.Bind<GachaLineupDialogUseCase>().AsCached();
            Container.Bind<GachaDetailDialogUseCase>().AsCached();
            Container.BindInterfacesTo<GachaRatioPageModelFactory>().AsCached();
            Container.BindInterfacesTo<GachaLineupPageModelFactory>().AsCached();
            Container.BindInterfacesTo<AnnouncementCellUseCaseModelFactory>().AsCached();
            Container.BindInterfacesTo<AnnouncementService>().AsCached();
            
            // ガチャ履歴
            Container.BindViewFactoryInfo<GachaHistoryDialogViewController, GachaHistoryDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<GachaHistoryDetailDialogViewController, GachaHistoryDetailDialogViewControllerInstaller>();
            Container.Bind<GachaHistoryWireFrame>().AsCached();
            Container.Bind<GachaHistoryDialogUseCase>().AsCached();
        }
    }
}
