using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.PersistentStateKomaEffectModel
{
    public class WeakeningKomaEffectLogic : IPersistentKomaEffectLogic
    {
        StateEffectType IPersistentKomaEffectLogic.GetStateEffectType()
        {
            return StateEffectType.Weakening;
        }

        StateEffect IPersistentKomaEffectLogic.GetStateEffect(StateEffectParameter effectParameter)
        {
            return new StateEffect(
                StateEffectType.Weakening,
                EffectiveCount.Infinity,
                EffectiveProbability.Hundred,
                TickCount.Infinity,
                effectParameter,
                StateEffectConditionValue.Empty,
                StateEffectConditionValue.Empty);
        }

        IReadOnlyList<StateEffectType> IPersistentKomaEffectLogic.GetBlockableStateEffectTypes()
        {
            return new List<StateEffectType> { StateEffectType.WeakeningBlock };
        }

        bool IPersistentKomaEffectLogic.IsTarget(CharacterUnitModel unit, StateEffectSourceId komaEffectSourceId)
        {
            // 弱体化無効の状態変化を持っている場合は、弱体化コマ滞在中毎フレームブロックエフェクトが発生するのを防ぐため、
            // コマ突入時または最後にブロックしたStateEffectSourceIdが異なる場合のみTargetにする
            if (unit.StateEffects.Any(effect => effect.Type == StateEffectType.WeakeningBlock))
            {
                // コマ突入時はtrue
                if (unit.LocatedKoma.Id != unit.PrevLocatedKoma.Id) return true;

                var weakeningBlock = unit.StateEffects.FirstOrDefault(effect => effect.Type == StateEffectType.WeakeningBlock)
                    as IBlockStateEffectModel;
                if (weakeningBlock == null) return true;

                return weakeningBlock.GetLastBlockedEffectSourceId() != komaEffectSourceId;
            }
            return true;
        }

        IStateEffectModel IPersistentKomaEffectLogic.UpdateDuration(IStateEffectModel stateEffectModel, TickCount duration)
        {
            if (stateEffectModel is StateEffectModel weakeningEffect)
            {
                return weakeningEffect with { Duration = duration };
            }

            return stateEffectModel;
        }
    }
}

