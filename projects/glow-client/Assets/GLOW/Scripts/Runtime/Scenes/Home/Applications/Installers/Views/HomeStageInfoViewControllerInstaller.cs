using GLOW.Scenes.EncyclopediaEmblemDetail.Application;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Views;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Home.Applications.Installers.Views
{
    internal sealed class HomeStageInfoViewControllerInstaller : Installer
    {
        [Inject] HomeStageInfoViewController.Argument Argument { get; set; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<HomeStageInfoViewController>();
            Container.BindInterfacesTo<HomeStageInfoPresenter>().AsCached();
        }
    }
}
