using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public interface IBlockStateEffectModel
    {
        StateEffectSourceId GetLastBlockedEffectSourceId();
        IStateEffectModel WithUpdatedLastBlockedEffectSourceId(StateEffectSourceId stateEffectSourceId);
    }
}
