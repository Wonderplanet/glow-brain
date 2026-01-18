using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record RushChargeModel(RushChargeCount ChargeCount, PercentageM DamageBonus, AttackHitType KnockBackType)
    {
        public static RushChargeModel Empty { get; } = new RushChargeModel(
            RushChargeCount.Empty,
            PercentageM.Empty,
            AttackHitType.Normal);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
