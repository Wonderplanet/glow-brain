using WPFramework.Application.Modules;
using WPFramework.Modules.Observability;
using Zenject;

namespace WPFramework.Application.Installers
{
    public sealed class FrameworkInstaller : MonoInstaller<FrameworkInstaller>
    {
        public override void InstallBindings()
        {
            // NOTE: システム全体で利用するリポジトリ
            Container.BindInterfacesTo<SystemInfoProvider>().AsCached();
            Container.BindInterfacesTo<ThermalStateObserver>().AsCached();

            Container.Install<AssetBundleInstaller>();
            Container.Install<LocalizationInstaller>();
            Container.Install<UIInstaller>();
            Container.Install<AudioInstaller>();
            Container.Install<SceneTransitionInstaller>();
            Container.Install<ErrorHandleInstaller>();
            Container.Install<NetworkInstaller>();
            Container.Install<InputInstaller>();
            Container.Install<SDKInstaller>();
        }
    }
}
