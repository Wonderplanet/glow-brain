using GLOW.Scenes.Home.Applications.Installers.Views;
using GLOW.Scenes.Home.Presentation.Views;
using WPFramework.Application.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.Home.Applications.Installers
{
    internal sealed class HomeSceneInstaller : MonoInstaller<HomeSceneInstaller>
    {
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(HomeSceneInstaller), nameof(InstallBindings));

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<HomeViewController, HomeViewControllerInstaller>();
        }
    }
}
