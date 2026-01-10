using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.PersistentStateKomaEffectModel
{
    public class PoisonKomaEffectLogic : IPersistentKomaEffectLogic
    {
        StateEffectType IPersistentKomaEffectLogic.GetStateEffectType()
        {
            return StateEffectType.Poison;
        }

        StateEffect IPersistentKomaEffectLogic.GetStateEffect(StateEffectParameter effectParameter)
        {
            return new StateEffect(
                StateEffectType.Poison,
                EffectiveCount.Infinity,
                EffectiveProbability.Hundred,
                TickCount.Infinity,
                effectParameter,
                StateEffectConditionValue.Empty,
                StateEffectConditionValue.Empty);
        }

        IReadOnlyList<StateEffectType> IPersistentKomaEffectLogic.GetBlockableStateEffectTypes()
        {
            return new List<StateEffectType> { StateEffectType.PoisonBlock };
        }

        bool IPersistentKomaEffectLogic.IsTarget(CharacterUnitModel unit, StateEffectSourceId komaEffectSourceId)
        {
            // 毒無効の状態変化を持っている場合は、毒コマ滞在中毎フレームブロックエフェクトが発生するのを防ぐため、
            // コマ突入時または最後にブロックしたStateEffectSourceIdが異なる場合のみTargetにする
            if (unit.StateEffects.Any(effect => effect.Type == StateEffectType.PoisonBlock))
            {
                // コマ突入時はtrue
                if (unit.LocatedKoma.Id != unit.PrevLocatedKoma.Id) return true;

                var poisonBlock = unit.StateEffects.FirstOrDefault(effect => effect.Type == StateEffectType.PoisonBlock)
                    as IBlockStateEffectModel;
                if (poisonBlock == null) return true;

                return poisonBlock.GetLastBlockedEffectSourceId() != komaEffectSourceId;
            }
            return true;
        }

        IStateEffectModel IPersistentKomaEffectLogic.UpdateDuration(IStateEffectModel stateEffectModel, TickCount duration)
        {
            if (stateEffectModel is PoisonDamageStateEffectModel poisonEffect)
            {
                return poisonEffect with { Duration = duration };
            }

            return stateEffectModel;
        }
    }
}
