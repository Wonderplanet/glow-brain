using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Data.Data;
using GLOW.Modules.Tutorial.Domain.ValueObject;

namespace GLOW.Modules.Tutorial.Data.DataStore
{
    public interface ITutorialSequenceDataStore
    {
        UniTask<TutorialSequenceDataList> LoadTutorialSequence(TutorialSequenceAssetPath assetPath, CancellationToken cancellationToken);
    }
}
