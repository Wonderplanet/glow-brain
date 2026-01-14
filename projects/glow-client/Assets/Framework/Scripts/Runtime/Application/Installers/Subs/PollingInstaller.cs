using WPFramework.Modules.Polling;
using Zenject;

namespace WPFramework.Application.Installers
{
    public class PollingInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindInterfacesAndSelfTo<PollingManager>().AsCached().NonLazy();
        }
    }
}
