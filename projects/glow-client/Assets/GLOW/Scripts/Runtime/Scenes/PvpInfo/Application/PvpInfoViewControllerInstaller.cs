using GLOW.Scenes.PvpInfo.Domain;
using GLOW.Scenes.PvpInfo.Domain.UseCase;
using GLOW.Scenes.PvpInfo.Presentation.Presenter;
using GLOW.Scenes.PvpInfo.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PvpInfo.Application
{
    public class PvpInfoViewControllerInstaller : Installer
    {
        [Inject] PvpInfoViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PvpInfoViewController>();
            Container.Bind<PvpInfoUseCase>().AsCached();
            Container.BindInterfacesTo<PvpInfoPresenter>().AsCached();
        }
    }
}
