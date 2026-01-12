using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IBuffStatePercentageConverter
    {
        public IReadOnlyList<PercentageM> ReduceBuffStateEffectCountAndGetPercentages(
            IReadOnlyList<IStateEffectModel> effects, out IReadOnlyList<IStateEffectModel> updatedEffects);

        public IReadOnlyList<PercentageM> ReduceDebuffStateEffectCountAndGetPercentages(
            IReadOnlyList<IStateEffectModel> effects, out IReadOnlyList<IStateEffectModel> updatedEffects);

        public IReadOnlyList<PercentageM> GetAttackPowerBuffPercentages(
            IReadOnlyList<IStateEffectModel> effects);

        public IReadOnlyList<PercentageM> GetAttackPowerDebuffPercentages(
            IReadOnlyList<IStateEffectModel> effects);

        public IReadOnlyList<PercentageM> GetUnitMoveSpeedBuffPercentages(
            IReadOnlyList<IStateEffectModel> effects);

        public IReadOnlyList<PercentageM> GetUnitMoveSpeedDebuffPercentages(
            IReadOnlyList<IStateEffectModel> effects);
    }
}
