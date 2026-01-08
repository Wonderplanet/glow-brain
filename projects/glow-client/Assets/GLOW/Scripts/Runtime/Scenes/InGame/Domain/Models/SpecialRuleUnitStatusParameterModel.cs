using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record SpecialRuleUnitStatusParameterModel(
        PercentageM HpParameter,
        PercentageM AttackPowerParameter,
        TickCount SpecialAttackCoolTimeParameter,
        TickCount SummonCoolTimeParameter)
    {
        public static SpecialRuleUnitStatusParameterModel Empty { get; } = new(
            PercentageM.Hundred,
            PercentageM.Hundred,
            TickCount.Empty,
            TickCount.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
