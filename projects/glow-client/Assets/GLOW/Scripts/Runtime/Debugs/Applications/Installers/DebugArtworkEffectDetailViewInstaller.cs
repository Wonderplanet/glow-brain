using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Scenes.DebugArtworkEffectDetail.Presentation;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.DebugStageDetail.Application
{
    public class DebugArtworkEffectDetailViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<DebugArtworkEffectDetailViewController>();
            Container.Bind<DebugArtworkEffectUseCase>().AsCached();
        }
    }
}