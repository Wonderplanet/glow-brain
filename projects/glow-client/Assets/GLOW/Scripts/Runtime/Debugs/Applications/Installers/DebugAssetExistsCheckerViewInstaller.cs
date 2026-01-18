#if GLOW_DEBUG
using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.Command.Presentations.Views.DebugAssetExistsCheckerView;
#endif //GLOW_DEBUG

#if GLOW_DEBUG
using WPFramework.Debugs.Profiler;
#endif //DEBUG

using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Debugs.Applications.Installers
{
    public class DebugAssetExistsCheckerViewInstaller : Installer
    {
#if GLOW_DEBUG

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<DebugAssetExistsCheckerViewController>();
            Container.Bind<DebugAssetExistsCheckerUseCase>().AsCached();
            Container.BindInterfacesTo<DebugAssetExistsCheckerPresenter>().AsCached();
        }
#else
        public override void InstallBindings()
        {
        }
#endif
    }
}
