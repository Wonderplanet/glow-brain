#if GLOW_INGAME_DEBUG
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    /// <summary>
    /// デバッグ用：全ユニットのノックバックを無効化するUseCase
    /// </summary>
    public sealed class DebugDisableKnockBackUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IStateEffectModelFactory StateEffectModelFactory { get; }

        /// <summary>
        /// 現在召喚されている全ユニット（プレイヤーキャラと敵キャラ）にノックバック耐性を付与し、DamageKnockBackCountを0にする
        /// </summary>
        public void DisableKnockBack(bool includeForcedKnockBack = false)
        {
            var knockBackBlockEffect = new StateEffect(
                StateEffectType.KnockBackBlock,
                EffectiveCount.Infinity,
                EffectiveProbability.Hundred,
                TickCount.Infinity,
                StateEffectParameter.Empty,
                StateEffectConditionValue.Empty,
                StateEffectConditionValue.Empty
            );

            var forcedKnockBackBlockEffect = new StateEffect(
                StateEffectType.ForcedKnockBackBlock,
                EffectiveCount.Infinity,
                EffectiveProbability.Hundred,
                TickCount.Infinity,
                StateEffectParameter.Empty,
                StateEffectConditionValue.Empty,
                StateEffectConditionValue.Empty
            );

            var updatedUnits = InGameScene.CharacterUnits.ToList();
            int affectedUnitCount = 0;

            for (int i = 0; i < updatedUnits.Count; i++)
            {
                var unit = updatedUnits[i];
                var updatedStateEffects = unit.StateEffects.ToList();

                var knockBackModel = StateEffectModelFactory.Create(
                    StateEffectSourceId.Empty,
                    knockBackBlockEffect,
                    false);
                
                updatedStateEffects.Add(knockBackModel);

                if (includeForcedKnockBack)
                {
                    var forceKnockBackModel = StateEffectModelFactory.Create(
                        StateEffectSourceId.Empty,
                        forcedKnockBackBlockEffect,
                        false);
                    
                    updatedStateEffects.Add(forceKnockBackModel);
                }

                var newUnit = unit with 
                { 
                    StateEffects = updatedStateEffects,
                    DamageKnockBackCount = KnockBackCount.Empty
                };

                updatedUnits[i] = newUnit;
                affectedUnitCount++;
            }

            InGameScene.CharacterUnits = updatedUnits;
        }
    }
}
#endif