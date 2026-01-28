using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Debugs.Command.Presentations.Views.DebugMstUnitStatusView;
using Zenject;
using UIKit.ZenjectBridge;

namespace GLOW.Scenes.DebugMstUnitStatus.Application
{
    public class DebugMstUnitStatusViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<DebugMstUnitStatusViewController>();
            Container.Bind<DebugMstUnitStatusUseCase>().AsCached();
            Container.BindInterfacesAndSelfTo<DebugMstUnitAttackStatusModelFactory>().AsCached();
            Container.BindInterfacesAndSelfTo<DebugMstUnitLevelStatusModelFactory>().AsCached();
            Container.BindInterfacesAndSelfTo<DebugMstUnitSpecialUnitSpecialParamModelFactory>().AsCached();
        }
    }
}
