#if GLOW_INGAME_DEBUG
using System.Linq;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    /// <summary>
    /// デバッグ用：指定ユニットに状態変化を付与するUseCase
    /// </summary>
    public sealed class DebugApplyStateEffectUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IStateEffectModelFactory StateEffectModelFactory { get; }

        /// <summary>
        /// 指定したFieldObjectIdのユニットに状態変化を付与する
        /// </summary>
        public void ApplyStateEffect(FieldObjectId fieldObjectId, StateEffect stateEffect)
        {
            var unit = InGameScene.CharacterUnits.FirstOrDefault(
                x => x.Id == fieldObjectId,
                CharacterUnitModel.Empty);
            if (unit.IsEmpty()) return;

            var effectModel = StateEffectModelFactory.Create(
                StateEffectSourceId.Empty,
                stateEffect,
                true);

            var newStateEffects = unit.StateEffects.Append(effectModel).ToList();
            var newUnit = unit with { StateEffects = newStateEffects };

            InGameScene.CharacterUnits = InGameScene.CharacterUnits.Replace(unit, newUnit);
        }
    }
}
#endif

