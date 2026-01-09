using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Data.Data;
using GLOW.Modules.Tutorial.Domain.ValueObject;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Modules.Tutorial.Data.DataStore
{
    public class TutorialSequenceDataStore : ITutorialSequenceDataStore
    {
        [Inject] IAssetSource AssetSource { get; }

        public async UniTask<TutorialSequenceDataList> LoadTutorialSequence(TutorialSequenceAssetPath assetPath, CancellationToken cancellationToken)
        {
            IAssetReference<TutorialSequenceDataList> assetReference = default;
            try
            {
                assetReference = await AssetSource.GetAsset<TutorialSequenceDataList>(cancellationToken, assetPath.Value);
                return assetReference.Value;
            }
            finally
            {
                assetReference?.Release();
            }
        }
    }
}
