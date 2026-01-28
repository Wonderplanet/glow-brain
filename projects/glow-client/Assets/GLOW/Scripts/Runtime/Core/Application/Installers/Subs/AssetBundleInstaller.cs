using GLOW.Core.Application.Settings;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class AssetBundleInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: アセットバンドルの管理システムをインストール
            Container.Bind<ICustomAssetBundleEncryptKeyProvider>().To<EncryptKeyProvider>().AsCached();
        }
    }
}
