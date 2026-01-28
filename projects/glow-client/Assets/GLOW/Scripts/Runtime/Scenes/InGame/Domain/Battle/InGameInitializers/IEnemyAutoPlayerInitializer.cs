using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IEnemyAutoPlayerInitializer
    {
        MstAutoPlayerSequenceModel Initialize(
            AutoPlayerSequenceSetId mstAutoPlayerSequenceSetId,
            MstPageModel mstPageModel,
            IStageEnemyParameterCoef stageEnemyParameterCoef,
            IReadOnlyList<DeckUnitModel> deckUnits);
    }
}
