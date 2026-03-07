using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record GimmickObjectToEnemyTransformationUpdateProcessResult(
        IReadOnlyList<InGameGimmickObjectModel> UpdatedGimmickObjectModels,
        IReadOnlyList<InGameGimmickObjectModel> TransformationStartedGimmickObjectModels,
        IReadOnlyList<GimmickObjectToEnemyTransformationModel> UpdatedGimmickObjectToEnemyTransformationModels,
        UnitSummonQueueModel UpdatedUnitSummonQueue,
        BossSummonQueueModel UpdatedBossSummonQueue)
    {
        public static GimmickObjectToEnemyTransformationUpdateProcessResult Empty { get; } = new (
            new List<InGameGimmickObjectModel>(),
            new List<InGameGimmickObjectModel>(),
            new List<GimmickObjectToEnemyTransformationModel>(),
            UnitSummonQueueModel.Empty,
            BossSummonQueueModel.Empty);
    }
}
