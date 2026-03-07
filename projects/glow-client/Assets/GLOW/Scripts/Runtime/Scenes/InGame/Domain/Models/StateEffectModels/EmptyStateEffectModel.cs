using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record EmptyStateEffectModel : IStateEffectModel
    {
        public static EmptyStateEffectModel Instance { get; } = new();

        public StateEffectId Id => StateEffectId.Empty;
        public StateEffectSourceId SourceId => StateEffectSourceId.Empty;
        public StateEffectType Type => StateEffectType.None;
        public EffectiveCount EffectiveCount => EffectiveCount.Empty;
        public EffectiveProbability EffectiveProbability => EffectiveProbability.Empty;
        public TickCount Duration => TickCount.Empty;
        public StateEffectParameter Parameter => StateEffectParameter.Empty;
        public IStateEffectConditionModel Condition => StateEffectEmptyConditionModel.Instance;
        public bool NeedsDisplay => false;

        public bool IsEmpty()
        {
            return true;
        }

        public IStateEffectModel WithDecreasedEffectiveCount()
        {
            return this;
        }

        public IStateEffectModel WithDecreasedDuration(TickCount tickCount)
        {
            return this;
        }

        public AttackData GenerateAttack()
        {
            return AttackData.Empty;
        }
    }
}
