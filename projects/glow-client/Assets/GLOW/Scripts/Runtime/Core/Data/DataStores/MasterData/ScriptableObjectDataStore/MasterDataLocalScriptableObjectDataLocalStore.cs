using System.Threading;
using Cysharp.Threading.Tasks;
// using WonderPlanet.ResourceManagement;
// using Zenject;

namespace GLOW.Core.Data.DataStores
{
    public class MasterDataScriptableObjectDataStore : IMstDataLocalDataStore
    {
        // [Inject] IAssetSource AssetSource { get; }
        
        public async UniTask Load(CancellationToken cancellationToken)
        {
            // 現状ローカルで読み込むものがないので空
            await UniTask.CompletedTask;
        }
    }
}
