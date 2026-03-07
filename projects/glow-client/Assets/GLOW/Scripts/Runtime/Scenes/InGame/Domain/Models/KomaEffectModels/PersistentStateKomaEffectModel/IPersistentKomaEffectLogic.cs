using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.PersistentStateKomaEffectModel
{
    public interface IPersistentKomaEffectLogic
    {
        StateEffectType GetStateEffectType();
        StateEffect GetStateEffect(StateEffectParameter effectParameter);
        IReadOnlyList<StateEffectType> GetBlockableStateEffectTypes();
        bool IsTarget(CharacterUnitModel unit, StateEffectSourceId komaEffectSourceId);
        IStateEffectModel UpdateDuration(IStateEffectModel stateEffectModel, TickCount duration);
    }
}
