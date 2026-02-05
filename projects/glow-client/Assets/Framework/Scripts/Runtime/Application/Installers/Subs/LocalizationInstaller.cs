using WPFramework.Modules.Localization;
using WPFramework.Modules.Localization.Terms;
using Zenject;

namespace WPFramework.Application.Installers
{
    internal sealed class LocalizationInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: ローカライズ機構の管理システムをインストール
            Container.BindInterfacesTo<LocalizationAssetManager>().AsCached();
            Container.BindInterfacesTo<LocalizationTermsManager>().AsCached();
        }
    }
}
