#if GLOW_INGAME_DEBUG

using GLOW.Debugs.InGame.Domain.UseCases;
using GLOW.Debugs.InGame.Presentation;
using GLOW.Debugs.InGame.Presentation.DebugIngameLogView;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Debugs.InGame.Installers
{
    public class DebugIngameLogViewerViewInstaller : Installer
    {
        [Inject] DebugIngameLogViewerViewController.Argument Args { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Args);
            Container.BindViewWithKernal<DebugIngameLogViewerViewController>();
            Container.BindInterfacesTo<DebugIngameLogViewerPresenter>().AsCached();
            Container.Bind<DebugIngameLogViewerUseCase>().AsCached();
        }
    }
}
#endif
