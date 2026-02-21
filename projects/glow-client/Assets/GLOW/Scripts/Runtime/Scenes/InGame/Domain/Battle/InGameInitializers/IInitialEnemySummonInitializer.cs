using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IInitialEnemySummonInitializer
    {
        InitialEnemySummonInitializerResult InitializeEnemySummon(
            MstAutoPlayerSequenceModel enemySequenceModel,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage,
            IMstInGameModel mstInGameModel);
    }
}
