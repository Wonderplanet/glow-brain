using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IGimmickObjectToEnemyTransformationUpdateProcess
    {
        GimmickObjectToEnemyTransformationUpdateProcessResult UpdateTransformation(
            IReadOnlyList<InGameGimmickObjectModel> gimmickObjectModels,
            IReadOnlyList<GimmickObjectToEnemyTransformationModel> gimmickObjectToEnemyTransformationModels,
            UnitSummonQueueModel unitSummonQueue,
            BossSummonQueueModel bossSummonQueue,
            TickCount tickCount);
    }
}
