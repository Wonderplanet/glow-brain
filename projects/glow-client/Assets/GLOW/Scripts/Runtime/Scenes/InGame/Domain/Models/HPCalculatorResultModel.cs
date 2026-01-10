using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record HPCalculatorResultModel(
        HP HP,
        Damage TotalDamage,
        Heal TotalHeal,
        IReadOnlyList<IStateEffectModel> UpdatedStateEffects,
        IReadOnlyList<HPCalculatorResultDetailModel> Details,
        SurvivedByGutsFlag IsSurvivedByGuts);
}
