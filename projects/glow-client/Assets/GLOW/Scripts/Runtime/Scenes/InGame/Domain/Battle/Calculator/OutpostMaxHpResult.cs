using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public record OutpostMaxHpResult(
        HP Hp,
        OutpostHpSpecialRuleFlag IsOverride
    )
    {
        public static OutpostMaxHpResult Empty { get; } = new (HP.Empty, OutpostHpSpecialRuleFlag.False);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}

