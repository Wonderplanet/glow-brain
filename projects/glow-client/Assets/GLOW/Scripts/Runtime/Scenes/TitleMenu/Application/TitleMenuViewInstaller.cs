using GLOW.Scenes.TitleMenu.Domain;
using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.TitleMenu.Presentation;

namespace GLOW.Scenes.TitleMenu.Application
{
    public class TitleMenuViewInstaller : Installer
    {
        [Inject] TitleMenuViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<TitleMenuViewController>();
            Container.BindInterfacesTo<TitleMenuPresenter>().AsCached();
        }
    }
}
