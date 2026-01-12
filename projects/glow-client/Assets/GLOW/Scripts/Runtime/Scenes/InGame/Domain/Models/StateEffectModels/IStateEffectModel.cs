using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public interface IStateEffectModel
    {
        StateEffectId Id { get; }
        StateEffectSourceId SourceId { get; }
        StateEffectType Type { get; }
        EffectiveCount EffectiveCount { get; }
        EffectiveProbability EffectiveProbability { get; }
        TickCount Duration { get; }
        StateEffectParameter Parameter { get; }
        IStateEffectConditionModel Condition { get; }
        bool NeedsDisplay { get; }

        bool IsEmpty();
        IStateEffectModel WithDecreasedEffectiveCount();
        IStateEffectModel WithDecreasedDuration(TickCount tickCount);
        AttackData GenerateAttack(); //HP減算、継続回復のStateで利用
    }
}
