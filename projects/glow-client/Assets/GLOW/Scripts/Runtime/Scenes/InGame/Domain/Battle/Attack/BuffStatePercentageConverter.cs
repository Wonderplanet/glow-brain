using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class BuffStatePercentageConverter : IBuffStatePercentageConverter
    {
        [Inject] IStateEffectChecker StateEffectChecker { get; }
        
        // ダメージ計算対象のAttackDamageType（Healとかを除く）
        static readonly StateEffectType[] AttackPowerUpStateEffectTypes = new[]
        {
            StateEffectType.AttackPowerUp,
            StateEffectType.AttackPowerUpInNormalKoma,
            StateEffectType.AttackPowerUpByHpPercentage,
        };

        public IReadOnlyList<PercentageM> ReduceBuffStateEffectCountAndGetPercentages(
            IReadOnlyList<IStateEffectModel> effects, out IReadOnlyList<IStateEffectModel> updatedEffects)
        {
            var percentages = new List<PercentageM>();
            updatedEffects = effects;
            
            foreach (var effectType in AttackPowerUpStateEffectTypes)
            {
                var attackPowerResult = StateEffectChecker.CheckAndReduceCount(effectType, updatedEffects);
                updatedEffects = attackPowerResult.UpdatedStateEffects;
                if (attackPowerResult.IsEffectActivated)
                {
                    percentages.AddRange(attackPowerResult.Parameters.Select(parameter => parameter.ToPercentageM()));
                }
            }
            
            return percentages;
        }

        public IReadOnlyList<PercentageM> ReduceDebuffStateEffectCountAndGetPercentages(
            IReadOnlyList<IStateEffectModel> effects, out IReadOnlyList<IStateEffectModel> updatedEffects)
        {
            var attackPowerDownResult = StateEffectChecker.CheckAndReduceCount(StateEffectType.AttackPowerDown, effects);
            updatedEffects = attackPowerDownResult.UpdatedStateEffects;
            if (attackPowerDownResult.IsEffectActivated)
            {
                return attackPowerDownResult.Parameters.Select(parameter => parameter.ToPercentageM()).ToList();
            }

            return Array.Empty<PercentageM>();
        }

        public IReadOnlyList<PercentageM> GetAttackPowerBuffPercentages(IReadOnlyList<IStateEffectModel> effects)
        {
            var percentages = new List<PercentageM>();
            
            foreach (var effectType in AttackPowerUpStateEffectTypes)
            {
                var attackPowerUpPercentages = StateEffectChecker.GetParameters(effectType, effects)
                    .Select(parameter => parameter.ToPercentageM());
                percentages.AddRange(attackPowerUpPercentages);
            }

            return percentages;
        }

        public IReadOnlyList<PercentageM> GetAttackPowerDebuffPercentages(IReadOnlyList<IStateEffectModel> effects)
        {
            var percentages = new List<PercentageM>();

            var attackPowerDownPercentages = StateEffectChecker.GetParameters(StateEffectType.AttackPowerDown, effects)
                .Select(parameter => parameter.ToPercentageM());
            percentages.AddRange(attackPowerDownPercentages);

            return percentages;
        }

        public IReadOnlyList<PercentageM> GetUnitMoveSpeedBuffPercentages(
            IReadOnlyList<IStateEffectModel> effects)
        {
            var percentages = new List<PercentageM>();

            var moveSpeedUpPercentages = StateEffectChecker.GetParameters(StateEffectType.MoveSpeedUp, effects)
                .Select(parameter => parameter.ToPercentageM());
            percentages.AddRange(moveSpeedUpPercentages);

            var moveSpeedUpInNormalKomaPercentages = StateEffectChecker.GetParameters(StateEffectType.MoveSpeedUpInNormalKoma, effects)
                .Select(parameter => parameter.ToPercentageM());
            percentages.AddRange(moveSpeedUpInNormalKomaPercentages);

            return percentages;
        }

        public IReadOnlyList<PercentageM> GetUnitMoveSpeedDebuffPercentages(
            IReadOnlyList<IStateEffectModel> effects)
        {
            var percentages = new List<PercentageM>();

            var moveSpeedDownPercentages = StateEffectChecker.GetParameters(StateEffectType.MoveSpeedDown, effects)
                .Select(parameter => parameter.ToPercentageM());
            percentages.AddRange(moveSpeedDownPercentages);

            return percentages;
        }
    }
}
