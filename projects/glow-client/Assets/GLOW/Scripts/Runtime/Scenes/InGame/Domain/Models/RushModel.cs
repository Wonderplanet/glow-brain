using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record RushModel(
        TickCount ChargeTime,
        TickCount RemainingChargeTime,
        RushChargeCount ChargeCount,
        RushChargeCount MaxChargeCount,
        CanExecuteRushFlag CanExecuteRushFlag,
        ExecuteRushFlag ExecuteRushFlag,
        IReadOnlyList<RushChargeModel> ChargeBonuses,
        PercentageM SpecialUnitBonus,
        RushPowerUpStateEffectBonus PowerUpStateEffectBonus,
        AttackData AttackData,
        RushCoefficient Coefficient,
        AttackPower MaxRushAttackPower)
    {
        public static RushModel Empty { get; } = new RushModel(
            TickCount.Empty,
            TickCount.Empty,
            RushChargeCount.Empty,
            RushChargeCount.Empty,
            CanExecuteRushFlag.False,
            ExecuteRushFlag.False,
            new List<RushChargeModel>(),
            PercentageM.Empty,
            RushPowerUpStateEffectBonus.Empty,
            AttackData.Empty,
            RushCoefficient.Empty,
            AttackPower.Empty
        );

        public PercentageM GetCurrentChargeBonus()
        {
            return ChargeBonuses.FirstOrDefault(bonus => bonus.ChargeCount == ChargeCount)?.DamageBonus ?? PercentageM.Empty;
        }

        public AttackHitType GetCurrentKnockBackType()
        {
            return ChargeBonuses.FirstOrDefault(knockBack => knockBack.ChargeCount == ChargeCount)?.KnockBackType ?? AttackHitType.KnockBack2;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
