using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class UnitAbilityProcess : IUnitAbilityProcess
    {
        [Inject] IStateEffectModelFactory StateEffectModelFactory { get; }
        [Inject] IStateEffectChecker StateEffectChecker { get; }

        public IReadOnlyList<CharacterUnitModel> UpdateUnitAbility(IReadOnlyList<CharacterUnitModel> units)
        {
            // キャラ特性による状態変化を付与／解除
            var updatedUnits = new List<CharacterUnitModel>();

            foreach (var unit in units)
            {
                var updatedUnit = UpdateCharacterUnitStateEffects(unit);
                updatedUnits.Add(updatedUnit);
            }

            return updatedUnits;
        }

        CharacterUnitModel UpdateCharacterUnitStateEffects(CharacterUnitModel unit)
        {
            // 特性設定がない場合は何もしない
            if (unit.Abilities.Count <= 0) return unit;
            
            // 今いるコマとキャラの状態によって発動する状態変化効果をキャラに付与する
            var updatedEffects = GetUpdatedCharacterUnitStateEffects(unit, unit.LocatedKoma);

            return unit with { StateEffects = updatedEffects };
        }

        List<IStateEffectModel> GetUpdatedCharacterUnitStateEffects(
            CharacterUnitModel unit,
            KomaModel currentLocatedKoma)
        {
            var notAlwaysAbilities = unit.Abilities
                .Where(ability => !ability.ArisesStateEffectOnceOnSummon)
                .ToList();
            var updatedEffects = new List<IStateEffectModel>();
            
            // 発動し続ける特性と外す特性を分ける
            var abilityConditionContext = new AbilityConditionContext(unit);
            var activeAbilities = notAlwaysAbilities
                .Where(ability => ability.ArisesStateEffectIn(currentLocatedKoma) ||
                                  ability.ArisesStateEffectConditionAchieved(abilityConditionContext))
                .ToList();
            
            // 既に付与されている状態変化効果のうち、特性が発動しているものと特性由来ではないものを残す
            var activeStateEffects = unit.StateEffects
                .Where(effect => activeAbilities.Any(ability => effect.SourceId == ability.StateEffectSourceId) ||
                                 notAlwaysAbilities.All(ability => effect.SourceId != ability.StateEffectSourceId))
                .ToList();
            updatedEffects.AddRange(activeStateEffects);
            
            // 現在のCharacterUnitActionでの非攻撃でのStateEffectの無効がTrueの場合は特性が発動していても付与しない
            if (unit.Action.IsNonAttackStateEffectInvalidation) return updatedEffects;

            foreach (var ability in activeAbilities)
            {
                // 既に付与されている場合はスキップ
                if (activeStateEffects.Any(e => e.SourceId == ability.StateEffectSourceId)) continue;
                
                var stateEffect = ability.GetStateEffect();
                if (stateEffect.IsEmpty()) continue;
            
                if (StateEffectChecker.ShouldAttachHasNotMultiState(stateEffect, updatedEffects))
                {
                    var effectModel = StateEffectModelFactory.Create(ability.StateEffectSourceId, stateEffect, true);
                    updatedEffects.Add(effectModel);
                }
            }

            return updatedEffects;
        }
    }
}
