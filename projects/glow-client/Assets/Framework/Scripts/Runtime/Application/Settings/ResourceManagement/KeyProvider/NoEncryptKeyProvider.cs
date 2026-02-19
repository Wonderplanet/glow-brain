using WonderPlanet.ResourceManagement;

namespace WPFramework.Application.Settings
{
    public class NoEncryptKeyProvider : ICustomAssetBundleEncryptKeyProvider
    {
        public string ProvideKey()
        {
            return string.Empty;
        }

        public int Version => 1;
        public bool UseEncryptCatalog => false;
    }
}
