using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Repositories;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class MstDataInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: マスタデータ管理システムをインストール
            Container.BindInterfacesTo<MasterDataScriptableObjectDataStore>().AsCached();
            
            Container.BindInterfacesTo<MstDataLocalJsonDataStore>()
                .FromInstance(new MstDataLocalJsonDataStore(enableDecryption: true))
                .AsCached();
            
            Container.BindInterfacesTo<MasterDataRepository>().AsCached();
            Container.BindInterfacesTo<MstModelInMemoryCache>().AsCached();
        }
    }
}
