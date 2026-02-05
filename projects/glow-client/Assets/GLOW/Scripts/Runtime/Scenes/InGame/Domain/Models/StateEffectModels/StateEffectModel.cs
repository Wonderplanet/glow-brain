using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record StateEffectModel(
        StateEffectId Id,
        StateEffectSourceId SourceId,
        StateEffectType Type,
        EffectiveCount EffectiveCount,
        EffectiveProbability EffectiveProbability,
        TickCount Duration,
        StateEffectParameter Parameter,
        IStateEffectConditionModel Condition,
        bool NeedsDisplay) : IStateEffectModel
    {
        public bool IsEmpty()
        {
            return false;
        }

        public IStateEffectModel WithDecreasedEffectiveCount()
        {
            return this with { EffectiveCount = EffectiveCount - 1 };
        }

        public IStateEffectModel WithDecreasedDuration(TickCount tickCount)
        {
            if (Duration.IsEmpty() || Duration.IsInfinity())
            {
                return this;
            }
            return this with { Duration = Duration - tickCount };
        }

        public AttackData GenerateAttack()
        {
            return AttackData.Empty;
        }
    }
}
