using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IStateEffectChecker
    {
        public StateEffectCheckerResult CheckAndReduceCount(
            StateEffectType checkType,
            IReadOnlyList<IStateEffectModel> effects);

        public StateEffectCheckerResult CheckAndReduceCount(
            StateEffectType checkType,
            IReadOnlyList<IStateEffectModel> effects,
            IStateEffectConditionContext context);

        public IReadOnlyList<StateEffectParameter> GetParameters(
            StateEffectType checkType,
            IReadOnlyList<IStateEffectModel> effects);

        public StateEffectCheckerResult CheckAndReduceCount(
            StateEffectType checkType,
            IReadOnlyList<IStateEffectModel> effects,
            IStateEffectConditionContext context,
            StateEffectSourceId stateEffectSourceId);

        public bool Check(StateEffectType checkType, IReadOnlyList<IStateEffectModel> effects);

        public bool ShouldAttachHasNotMultiState(StateEffect target, IReadOnlyList<IStateEffectModel> hasEffects);

        public bool ShouldAttachInInvincible(bool isInvincible, StateEffect target);
    }
}
