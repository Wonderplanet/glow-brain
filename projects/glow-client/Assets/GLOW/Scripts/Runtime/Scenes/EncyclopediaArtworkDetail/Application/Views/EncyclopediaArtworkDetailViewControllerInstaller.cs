using GLOW.Scenes.ArtworkExpandDialog.Application.Views;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.Views;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Application.Views;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.Views;
using GLOW.Scenes.OutpostEnhance.Domain.UseCases;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Application.Views
{
    public class EncyclopediaArtworkDetailViewControllerInstaller : Installer
    {
        [Inject] EncyclopediaArtworkDetailViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EncyclopediaArtworkDetailViewController>();
            Container.BindInterfacesTo<EncyclopediaArtworkDetailPresenter>().AsCached();
            Container.Bind<GetEncyclopediaArtworkDetailUseCase>().AsCached();
            Container.Bind<SetArtworkFragmentDropQuestUseCase>().AsCached();
            Container.Bind<ApplyUpdatedOutpostArtworkUseCase>().AsCached();
            Container.Bind<InitializeEncyclopediaArtworkCacheUseCase>().AsCached();
            Container.Bind<ReceiveEncyclopediaFirstCollectionRewardUseCase>().AsCached();


            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<OutpostArtworkChangeConfirmViewController, OutpostArtworkChangeConfirmViewControllerInstaller>();
            Container.BindViewFactoryInfo<ArtworkExpandDialogViewController, ArtworkExpandDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<EncyclopediaArtworkPageViewController, EncyclopediaArtworkPageViewControllerInstaller>();

            Container.BindInstance(Argument).AsCached();
        }
    }
}
