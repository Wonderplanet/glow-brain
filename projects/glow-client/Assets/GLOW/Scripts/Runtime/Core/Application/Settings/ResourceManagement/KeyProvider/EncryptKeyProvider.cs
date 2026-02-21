using GLOW.Core.Constants;
using WonderPlanet.ResourceManagement;

namespace GLOW.Core.Application.Settings
{
    public sealed class EncryptKeyProvider : ICustomAssetBundleEncryptKeyProvider
    {
        string ICustomAssetBundleEncryptKeyProvider.ProvideKey()
        {
            return Credentials.AddressablesEncryptionKey;
        }

        int ICustomAssetBundleEncryptKeyProvider.Version => 1;

        bool ICustomAssetBundleEncryptKeyProvider.UseEncryptCatalog => true;
    }
}
