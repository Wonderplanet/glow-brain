using GLOW.Core.Application.Configs;
using GLOW.Core.Application.ErrorHandle.Handlers;
using GLOW.Core.Data.Services;
using GLOW.Core.Domain.Modules.Environment;
using WPFramework.Application.Configs;
using WPFramework.Domain.Translators;
using WPFramework.Modules.Environment;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public class EnvironmentInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindInterfacesTo<EnvironmentListTranslator>().AsCached();
            Container.BindInterfacesTo<EnvironmentTranslator>().AsCached();
            Container.BindInterfacesTo<EnvironmentResolver>().AsCached();
            Container.BindInterfacesTo<EnvironmentHostResolver>().AsCached();
            Container.Bind<EnvironmentCoordinator>().AsCached();
            Container.BindInterfacesTo<EncryptedEnvironmentDataParser>().AsCached();
            Container.BindInterfacesTo<EnvironmentService>().AsCached();
            Container.BindInterfacesTo<NetworkEnvironmentErrorHandler>().AsCached();
        }
    }
}