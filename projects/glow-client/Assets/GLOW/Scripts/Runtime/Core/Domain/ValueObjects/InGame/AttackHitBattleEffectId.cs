using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackHitBattleEffectId(BattleEffectId Value)
    {
        public static AttackHitBattleEffectId Empty { get; } = new(BattleEffectId.None);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
