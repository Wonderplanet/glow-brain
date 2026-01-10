using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Model;
using GLOW.Modules.Tutorial.Domain.ValueObject;

namespace GLOW.Modules.Tutorial.Domain.Repository
{
    public interface ITutorialSequenceRepository
    {
        public UniTask<IReadOnlyList<TutorialSequenceModel>> LoadTutorialSequence(TutorialSequenceAssetPath assetPath,
            CancellationToken cancellationToken);
    }
}
