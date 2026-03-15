using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.PersistentStateKomaEffectModel
{
    public class BurnKomaEffectLogic : IPersistentKomaEffectLogic
    {
        StateEffectType IPersistentKomaEffectLogic.GetStateEffectType()
        {
            return StateEffectType.Burn;
        }

        StateEffect IPersistentKomaEffectLogic.GetStateEffect(StateEffectParameter effectParameter)
        {
            return new StateEffect(
                StateEffectType.Burn,
                EffectiveCount.Infinity,
                EffectiveProbability.Hundred,
                TickCount.Infinity,
                effectParameter,
                StateEffectValue.Empty,
                StateEffectConditionValue.Empty,
                StateEffectConditionValue.Empty);
        }

        IReadOnlyList<StateEffectType> IPersistentKomaEffectLogic.GetBlockableStateEffectTypes()
        {
            // TODO BurnBlock未実装、実装時PoisonBlockと同様の処理・テストを追加すること
            return new List<StateEffectType> { StateEffectType.Unbeatable, StateEffectType.BurnBlock };
        }

        bool IPersistentKomaEffectLogic.IsTarget(CharacterUnitModel unit, StateEffectSourceId komaEffectSourceId)
        {
            // TODO BurnBlock未実装、実装時PoisonBlockと同様の処理・テストを追加すること
            // 無敵の状態変化を持っている場合処理(無敵優先)
            if (unit.StateEffects.Any(effect => effect.Type == StateEffectType.Unbeatable))
            {
                // コマ突入時はtrue
                if (unit.LocatedKoma.Id != unit.PrevLocatedKoma.Id) return true;

                var unbeatable = unit.StateEffects.FirstOrDefault(effect => effect.Type == StateEffectType.Unbeatable)
                    as IBlockStateEffectModel;
                if (unbeatable == null) return true;

                return unbeatable.GetLastBlockedEffectSourceId() != komaEffectSourceId;
            }
            
            return true;
        }

        IStateEffectModel IPersistentKomaEffectLogic.UpdateDuration(IStateEffectModel stateEffectModel, TickCount duration)
        {
            if (stateEffectModel is BurnDamageStateEffectModel poisonEffect)
            {
                return poisonEffect with { Duration = duration };
            }

            return stateEffectModel;
        }
    }
}
