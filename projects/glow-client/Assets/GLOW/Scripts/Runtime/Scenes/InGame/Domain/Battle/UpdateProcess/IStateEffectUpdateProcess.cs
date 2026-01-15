using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IStateEffectUpdateProcess
    {
        IReadOnlyList<CharacterUnitModel> UpdateStateEffects(
            IReadOnlyList<CharacterUnitModel> characterUnits,
            TickCount tickCount);
    }
}
