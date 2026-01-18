using GLOW.Core.Domain.Modules.Region;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    internal sealed class LocalizationLocaleSelectInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: アプリケーションのロケールを提供する処理を適用
            Container.BindInterfacesTo<LocalizationRegionProvider>().AsCached();
        }
    }
}
