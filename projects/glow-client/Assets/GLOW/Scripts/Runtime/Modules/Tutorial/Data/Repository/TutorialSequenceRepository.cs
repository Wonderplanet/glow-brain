using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Data.Data;
using GLOW.Modules.Tutorial.Data.DataStore;
using GLOW.Modules.Tutorial.Data.Translator;
using GLOW.Modules.Tutorial.Domain.Model;
using GLOW.Modules.Tutorial.Domain.Repository;
using GLOW.Modules.Tutorial.Domain.ValueObject;
using Zenject;

namespace GLOW.Modules.Tutorial.Data.Repository
{
    public class TutorialSequenceRepository : ITutorialSequenceRepository
    {
        [Inject] ITutorialSequenceDataStore TutorialSequenceDataStore { get; }

        public async UniTask<IReadOnlyList<TutorialSequenceModel>> LoadTutorialSequence(TutorialSequenceAssetPath assetPath,
            CancellationToken cancellationToken)
        {
            TutorialSequenceDataList dataList =
                await TutorialSequenceDataStore.LoadTutorialSequence(assetPath, cancellationToken);

            return TutorialSequenceModelTranslator.TranslateToTutorialSequenceModel(dataList);
        }
    }
}
