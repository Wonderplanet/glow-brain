using WPFramework.Modules.Log;
using GLOW.Scenes.Splash.Domain.UseCase;
using GLOW.Scenes.Splash.Presentation.Views;
using WPFramework.Application.Modules;
using Zenject;

#if GLOW_DEBUG
using GLOW.Debugs.Environment.Domain.UseCases;
#endif // GLOW_DEBUG

namespace GLOW.Scenes.Splash.Application.Installers
{
    internal class SplashSceneInstaller : MonoInstaller<SplashSceneInstaller>
    {
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(SplashSceneInstaller), nameof(InstallBindings));

            Container.Bind<SetUpUserPropertyUseCase>().AsCached();
            Container.Bind<BuildEnvironmentUseCase>().AsCached();
            
            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<SplashViewController, SplashViewControllerInstaller>();
            
#if GLOW_DEBUG
            Container.Bind<DebugBuildEnvironmentUseCase>().AsCached();
#endif // GLOW_DEBUG
        }
    }
}
