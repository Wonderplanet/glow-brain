using GLOW.Scenes.EncyclopediaArtworkDetail.Application.Views;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using GLOW.Scenes.InGame.Data.DataStores;
using GLOW.Scenes.InGame.Data.Repositories;
using GLOW.Scenes.OutpostEnhance.Domain.UseCases;
using GLOW.Scenes.OutpostEnhance.Presentation.Presenters;
using GLOW.Scenes.OutpostEnhance.Presentation.Views;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Application.Views;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using WPFramework.Modules.Log;

namespace GLOW.Scenes.OutpostEnhance.Application.Views
{
    public class OutpostEnhanceViewControllerInstaller : Zenject.Installer
    {
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(OutpostEnhanceViewControllerInstaller), "InstallBindings");

            Container.BindViewWithKernal<OutpostEnhanceViewController>();
            Container.BindInterfacesTo<OutpostEnhancePresenter>().AsCached();

            Container.Bind<GetOutpostEnhanceModelUseCase>().AsCached();
            Container.Bind<GetOutpostEnhanceArtworkListUseCase>().AsCached();
            Container.Bind<OutpostEnhanceUseCase>().AsCached();
            Container.Bind<GetCurrentOutpostArtworkUseCase>().AsCached();
            Container.Bind<ChangeOutpostArtworkUseCase>().AsCached();
            Container.Bind<InitializeOutpostArtworkCacheUseCase>().AsCached();
            Container.Bind<ApplyUpdatedOutpostArtworkUseCase>().AsCached();
            Container.Bind<UpdateDisplayedArtworkUseCase>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<OutpostEnhanceLevelUpDialogViewController, OutpostEnhanceLevelUpDialogControllerInstaller>();
            Container.BindViewFactoryInfo<EncyclopediaArtworkDetailViewController, EncyclopediaArtworkDetailViewControllerInstaller>();
        }
    }
}
