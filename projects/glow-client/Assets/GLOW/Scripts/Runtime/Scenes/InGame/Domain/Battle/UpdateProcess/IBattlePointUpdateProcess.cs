using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IBattlePointUpdateProcess
    {
        BattlePointUpdateProcessResult UpdateBattlePoint(
            BattlePointModel bpModel,
            BattlePointModel pvpOpponentBpModel,
            OutpostEnhancementModel enhancementModel,
            OutpostEnhancementModel pvpOpponentEnhancementModel,
            IReadOnlyList<CharacterUnitModel> deadCharacterUnits,
            TickCount tickCount);
    }
}
