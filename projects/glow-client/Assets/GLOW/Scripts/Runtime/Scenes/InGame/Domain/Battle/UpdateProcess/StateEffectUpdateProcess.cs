using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class StateEffectUpdateProcess : IStateEffectUpdateProcess
    {
        public IReadOnlyList<CharacterUnitModel> UpdateStateEffects(
            IReadOnlyList<CharacterUnitModel> characterUnits,
            TickCount tickCount)
        {
            var updatedCharacterUnits = new List<CharacterUnitModel>();

            foreach (var characterUnit in characterUnits)
            {
                if (UpdateDurationAndRemove(characterUnit.StateEffects, tickCount, out var updatedEffect))
                {
                    var updatedCharacterUnit = characterUnit with
                    {
                        StateEffects = updatedEffect
                    };

                    updatedCharacterUnits.Add(updatedCharacterUnit);
                }
                else
                {
                    updatedCharacterUnits.Add(characterUnit);
                }
            }

            return updatedCharacterUnits;
        }

        bool UpdateDurationAndRemove(
            IReadOnlyList<IStateEffectModel> effects, TickCount tickCount, out List<IStateEffectModel> updatedEffects)
        {
            updatedEffects = new List<IStateEffectModel>();
            bool updated = false;

            foreach (var effect in effects)
            {
                // 今回の更新で効果時間が切れるものは取り除く
                if (effect.Duration <= tickCount)
                {
                    updated = true;
                    continue;
                }

                // 効果時間を更新
                var updatedEffect = effect.WithDecreasedDuration(tickCount);
                updatedEffects.Add(updatedEffect);

                updated |= effect != updatedEffect;
            }

            return updated;
        }
    }
}
