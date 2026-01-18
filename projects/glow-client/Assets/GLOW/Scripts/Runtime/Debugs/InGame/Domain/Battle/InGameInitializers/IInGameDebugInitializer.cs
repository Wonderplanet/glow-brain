#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Debugs.InGame.Domain.Battle.InGameInitializers
{
    public interface IInGameDebugInitializer
    {
        InGameDebugModel Initialize(
            IMstInGameModel mstInGameModel,
            MstAutoPlayerSequenceModel enemyAutoPlayerSequenceModel,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            InGameType inGameType);
    }
}
#endif // GLOW_INGAME_DEBUG
