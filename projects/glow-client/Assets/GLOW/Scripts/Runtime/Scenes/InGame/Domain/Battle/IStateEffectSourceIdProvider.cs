using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IStateEffectSourceIdProvider
    {
        StateEffectSourceId GenerateNewId();
    }
}
