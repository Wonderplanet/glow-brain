using System;
using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WPFramework.Modules.Log;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess.ImmediateEffectHandler
{
    public class RemoveEffectHandler : IImmediateEffectHandler
    {
        public ImmediateEffectHandlerResult Handle(
            IAttackResultModel attackResult,
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits)
        {
            var emptyResult = ImmediateEffectHandlerResult.CreateEmpty(playerDeckUnits, pvpOpponentDeckUnits, characterUnits);

            // HitAttackResultModel（Direct設定想定）
            if (attackResult is not HitAttackResultModel hitAttackResult) return emptyResult;

            // 盤面上対象
            var targetUnit = characterUnits.FirstOrDefault(u => u.Id == hitAttackResult.TargetId, CharacterUnitModel.Empty);
            if (targetUnit.IsEmpty()) return emptyResult;

            // 解除対象(effect_valueが空なら何もしない)
            var stateEffect = attackResult.StateEffect;
            var stateEffectType = stateEffect.Type;
            var effectValue = stateEffect.Value.Value;
            if (string.IsNullOrEmpty(effectValue)) return emptyResult;

            // 解除個数
            var maxRemoveCount = stateEffect.EffectiveCount;

            // 解除対象StateEffectを選択
            IReadOnlyList<IStateEffectModel> removableEffects;
            if (effectValue == "All")
            {
                // All指定
                removableEffects = SelectRemovableEffectsByAll(targetUnit, stateEffectType, maxRemoveCount);
            }
            else
            {
                // StateEffectType指定（カンマ区切り）
                removableEffects = SelectRemovableEffectsByTypes(targetUnit, effectValue, stateEffectType, maxRemoveCount);
            }

            // 解除対象がいなければ何もしない
            if (removableEffects.IsEmpty()) return emptyResult;

            // StateEffectを解除
            var updatedStateEffects = targetUnit.StateEffects
                .Where(effect => !removableEffects.Contains(effect))
                .ToList();
            var updatedUnit = targetUnit with { StateEffects = updatedStateEffects };
            var updatedCharacterUnits = characterUnits
                .Select(u => u.Id == targetUnit.Id ? updatedUnit : u)
                .ToList();

            var appliedResult = new AppliedCharacterImmediateEffectResultModel(
                targetUnit.Id,
                targetUnit.BattleSide,
                stateEffectType);

            return new ImmediateEffectHandlerResult(
                playerDeckUnits,
                pvpOpponentDeckUnits,
                updatedCharacterUnits,
                AppliedDeckStateEffectResultModel.Empty,
                appliedResult);
        }

        IReadOnlyList<IStateEffectModel> SelectRemovableEffectsByAll(
            CharacterUnitModel targetUnit,
            StateEffectType handlerType,
            EffectiveCount maxRemoveCount)
        {
            // 除外対象SourceId取得
            var excludedSourceIds = GetExcludedSourceIds(targetUnit);
            var removableEffects = new List<IStateEffectModel>();

            // 新しい順（StateEffectIdの降順）に走査
            var sortedEffects = targetUnit.StateEffects .OrderByDescending(effect => effect.Id.Value) .ToList();
            foreach (var effect in sortedEffects)
            {
                // 解除数指定がある場合、規定数に達したらループ抜ける
                if (!maxRemoveCount.IsInfinity() && removableEffects.Count >= maxRemoveCount.Value)
                {
                    break;
                }

                // 除外対象チェック
                if (excludedSourceIds.Contains(effect.SourceId)) { continue; }

                // RemoveBuff: バフのみ、RemoveDebuff: デバフのみ
                bool isTarget = handlerType switch
                {
                    StateEffectType.RemoveBuff => effect.Type.IsRemovableBuff(),
                    StateEffectType.RemoveDebuff => effect.Type.IsRemovableDebuff(),
                    _ => false
                };

                if (isTarget)
                {
                    removableEffects.Add(effect);
                }
            }

            return removableEffects;
        }

        IReadOnlyList<IStateEffectModel> SelectRemovableEffectsByTypes(
            CharacterUnitModel targetUnit,
            string effectValue,
            StateEffectType handlerType,
            EffectiveCount maxRemoveCount)
        {
            // effectValueをパース(パース失敗したら空リストを返す)
            IReadOnlyList<StateEffectType> targetTypes;
            try
            {
                targetTypes = EnumListTranslator.ToEnumList<StateEffectType>(effectValue);
            }
            catch
            {
                targetTypes = Array.Empty<StateEffectType>();
            }
            if (targetTypes.IsEmpty()) { return Array.Empty<IStateEffectModel>(); }

            // 指定された解除対象がhandlerTypeに該当するか確認
            bool isValid = handlerType switch
            {
                StateEffectType.RemoveBuff => targetTypes.All(type => type.IsRemovableBuff()),
                StateEffectType.RemoveDebuff => targetTypes.All(type => type.IsRemovableDebuff()),
                _ => false
            };
            if (!isValid)
            {
                // StateEffectTypeに合致しないタイプが含まれている場合(データ設定ミス対策)
                ApplicationLog.LogError(nameof(RemoveEffectHandler), ZString.Format("Invalid effect types specified for handler type {0}: {1}", handlerType, effectValue));
                return Array.Empty<IStateEffectModel>();
            }

            // 除外対象SourceId取得
            var excludedSourceIds = GetExcludedSourceIds(targetUnit);
            var removableEffects = new List<IStateEffectModel>();

            // 新しい順（StateEffectIdの降順）に走査
            var sortedEffects = targetUnit.StateEffects .OrderByDescending(effect => effect.Id.Value) .ToList();
            foreach (var effect in sortedEffects)
            {
                // 解除数指定がある場合、規定数に達したらループ抜ける
                if (!maxRemoveCount.IsInfinity() && removableEffects.Count >= maxRemoveCount.Value)
                {
                    break;
                }

                // 除外対象チェック
                if (excludedSourceIds.Contains(effect.SourceId)) { continue; }

                // 指定されたタイプかどうか
                if (targetTypes.Contains(effect.Type))
                {
                    removableEffects.Add(effect);
                }
            }

            return removableEffects;
        }

        HashSet<StateEffectSourceId> GetExcludedSourceIds(CharacterUnitModel targetUnit)
        {
            var excludedSourceIds = new HashSet<StateEffectSourceId>();

            // 特性由来のStateEffect
            foreach (var ability in targetUnit.Abilities)
            {
                excludedSourceIds.Add(ability.StateEffectSourceId);
            }

            // コマ由来のStateEffect（解除時にいるコマから付与された効果）
            if (!targetUnit.LocatedKoma.IsEmpty())
            {
                excludedSourceIds.Add(targetUnit.LocatedKoma.StateEffectSourceId);
            }

            return excludedSourceIds;
        }
    }
}

