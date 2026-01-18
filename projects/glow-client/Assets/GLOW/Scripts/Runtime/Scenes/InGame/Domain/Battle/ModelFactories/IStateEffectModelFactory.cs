using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IStateEffectModelFactory
    {
        IStateEffectModel Create(StateEffectSourceId sourceId, StateEffect stateEffect, bool needsDisplay);
    }
}
