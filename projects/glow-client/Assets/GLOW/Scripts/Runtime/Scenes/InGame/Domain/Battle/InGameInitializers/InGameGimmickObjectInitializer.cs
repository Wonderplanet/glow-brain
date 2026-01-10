using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class InGameGimmickObjectInitializer : IInGameGimmickObjectInitializer
    {
        [Inject] IInGameGimmickObjectGenerationModelFactory InGameGimmickObjectGenerationModelFactory { get; }
        [Inject] IInGameGimmickObjectFactory InGameGimmickObjectFactory { get; }
        [Inject] IMstInGameGimmickObjectDataRepository MstInGameGimmickObjectDataRepository { get; }

        IReadOnlyList<InGameGimmickObjectModel> IInGameGimmickObjectInitializer.Initialize(
            MstAutoPlayerSequenceModel mstAutoPlayerSequenceModel,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage)
        {
            var initialGimmickObjectSummonElements = mstAutoPlayerSequenceModel.GimmickObjectSummonElements
                .Where(e => e.ActivationCondition.Type == AutoPlayerSequenceConditionType.InitialSummon)
                .Where(e => e.SummonPosition != FieldCoordV2.Empty);

            var gimmickObjectModels = new List<InGameGimmickObjectModel>();
            foreach (var autoPlayerSequenceElement in initialGimmickObjectSummonElements)
            {
                var generationModel = InGameGimmickObjectGenerationModelFactory.Create(autoPlayerSequenceElement);
                var mstGimmickObjectModel = MstInGameGimmickObjectDataRepository.GetMstInGameGimmickObjectModel(generationModel.MstInGameGimmickObjectId);
                var gimmickObjectModel = InGameGimmickObjectFactory.Generate(
                    generationModel,
                    mstGimmickObjectModel.AssetKey,
                    komaDictionary,
                    mstPage);
                gimmickObjectModels.Add(gimmickObjectModel);
            }

            return gimmickObjectModels;
        }
    }
}
