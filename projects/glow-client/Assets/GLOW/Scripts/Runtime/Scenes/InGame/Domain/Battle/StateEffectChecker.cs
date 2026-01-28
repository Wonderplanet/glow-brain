using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class StateEffectChecker : IStateEffectChecker
    {
        [Inject] IRandomProvider RandomProvider { get; }

        public StateEffectCheckerResult CheckAndReduceCount(
            StateEffectType checkType,
            IReadOnlyList<IStateEffectModel> effects)
        {
            return CheckAndReduceCount(checkType, effects, StateEffectEmptyConditionContext.Instance, StateEffectSourceId.Empty);
        }

        public StateEffectCheckerResult CheckAndReduceCount(
            StateEffectType checkType,
            IReadOnlyList<IStateEffectModel> effects,
            IStateEffectConditionContext context)
        {
            return CheckAndReduceCount(checkType, effects, context, StateEffectSourceId.Empty);
        }

        public StateEffectCheckerResult CheckAndReduceCount(
            StateEffectType checkType,
            IReadOnlyList<IStateEffectModel> effects,
            IStateEffectConditionContext context,
            StateEffectSourceId stateEffectSourceId)
        {
            var parameterList = new List<StateEffectParameter>();
            var updatedEffectList = new List<IStateEffectModel>();
            var isEffectActivated = false;

            if (effects.All(effect => !(effect.Type == checkType && !effect.EffectiveCount.IsZero() && !effect.Duration.IsZero())))
            {
                updatedEffectList.AddRange(effects);
                return new StateEffectCheckerResult(updatedEffectList, parameterList, EffectActivatedFlag.False);
            }

            foreach (var effect in effects)
            {
                if (effect.Type != checkType)
                {
                    updatedEffectList.Add(effect);
                    continue;
                }

                // 効果時間が0だったら無視（効果時間の更新時に取り除かれてるはずなので、本来は効果時間0のものはないはず）
                if (effect.Duration.IsZero())
                {
                    updatedEffectList.Add(effect);
                    continue;
                }

                // 効果回数か0だったら取り除く
                if (effect.EffectiveCount.IsZero())
                {
                    continue;
                }

                // 適用条件をチェック
                if (!effect.Condition.MeetsCondition(context))
                {
                    updatedEffectList.Add(effect);
                    continue;
                }

                // 効果が発動するかどうかを決める
                isEffectActivated = IsEffectActivated(effect.EffectiveProbability);

                var updatedEffect = effect;
                // 効果値を追加
                if (isEffectActivated)
                {
                    parameterList.Add(updatedEffect.Parameter);
                    updatedEffect = UpdateLastBlockStateEffectSourceId(effect, stateEffectSourceId);
                }

                // 効果回数が無限だったらそのまま残す
                if (updatedEffect.EffectiveCount.IsInfinity())
                {
                    updatedEffectList.Add(updatedEffect);
                    continue;
                }

                // 効果回数を使い切る場合は取り除く
                if ((updatedEffect.EffectiveCount - 1).IsZero())
                {
                    continue;
                }

                // 効果回数を減らす
                var updatedEffectDecreasedEffectiveCount = updatedEffect.WithDecreasedEffectiveCount();

                updatedEffectList.Add(updatedEffectDecreasedEffectiveCount);
            }

            return new StateEffectCheckerResult(updatedEffectList, parameterList, new EffectActivatedFlag(isEffectActivated));
        }

        public IReadOnlyList<StateEffectParameter> GetParameters(
            StateEffectType checkType,
            IReadOnlyList<IStateEffectModel> effects)
        {
            var parameterList = new List<StateEffectParameter>();
            foreach (var effect in effects)
            {
                if (effect.Type != checkType || effect.EffectiveCount.IsZero() || effect.Duration.IsZero()) continue;

                var isEffectActivated = IsEffectActivated(effect.EffectiveProbability);
                if (isEffectActivated) parameterList.Add(effect.Parameter);
            }

            return parameterList;
        }

        public bool Check(StateEffectType checkType, IReadOnlyList<IStateEffectModel> effects)
        {
            return effects.Any(effect =>
                effect.Type == checkType
                && !effect.EffectiveCount.IsZero()
                && !effect.Duration.IsZero());
        }

        public bool ShouldAttachHasNotMultiState(StateEffect target, IReadOnlyList<IStateEffectModel> hasEffects)
        {
            if (!target.Type.HasNotMulti()) return true;

            return hasEffects
                .Where(e => e.Type.HasNotMulti())
                .All(e =>e.Type != target.Type);
        }
        public bool ShouldAttachInInvincible(
            bool isInvincible, StateEffect target)
        {
            if (isInvincible)
            {
                return target.Type != StateEffectType.Poison &&
                       target.Type != StateEffectType.Burn;
            }
            else return true;
        }

        bool IsEffectActivated(EffectiveProbability effectiveProbability)
        {
            if (effectiveProbability.IsHundredOrMore())
            {
                return true;
            }
            if (effectiveProbability.IsZero())
            {
                return false;
            }
            return RandomProvider.Trial(effectiveProbability.ToPercentage());
        }

        /// <summary>
        /// コマ効果をブロックする状態変化を持っている場合、コマ滞在中に毎フレームブロックが発生するのを防ぐため、
        /// 最後にブロックしたStateEffectSourceIdを保持しておく
        /// </summary>
        /// <param name="effect"></param>
        /// <param name="stateEffectSourceId"></param>
        /// <returns></returns>
        IStateEffectModel UpdateLastBlockStateEffectSourceId(IStateEffectModel effect, StateEffectSourceId stateEffectSourceId)
        {
            if (effect is not IBlockStateEffectModel blockEffect || stateEffectSourceId.IsEmpty())
            {
                return effect;
            }

            return blockEffect.WithUpdatedLastBlockedEffectSourceId(stateEffectSourceId);
        }
    }
}
