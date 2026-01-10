using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Extensions;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class AttackPowerCalculator
    {
        public static AttackPower CalculateAttackPower(
            AttackPower basePower,
            AttackPowerParameter powerParameter,
            IReadOnlyList<PercentageM> buffPercentages,
            IReadOnlyList<PercentageM> debuffPercentages,
            CharacterColorAdvantageAttackBonus colorAdvantageAttackBonus,
            HP maxHp)
        {
            var power = CalculateAttackPower(basePower, powerParameter, buffPercentages, debuffPercentages, maxHp);
            if (!colorAdvantageAttackBonus.IsEmpty())
            {
                power = AttackPower.Max(power * colorAdvantageAttackBonus, AttackPower.LowerLimitWithDebuff);
            }

            return power;
        }

        public static AttackPower CalculateAttackPower(
            AttackPower basePower,
            AttackPowerParameter powerParameter,
            IReadOnlyList<PercentageM> buffPercentages,
            IReadOnlyList<PercentageM> debuffPercentages,
            HP maxHp)
        {
            var power = powerParameter.Type switch
            {
                AttackPowerParameterType.Fixed => powerParameter.ToAttackPower(),
                AttackPowerParameterType.MaxHpPercentage => maxHp.ToAttackPower() * powerParameter.ToPercentageM(),
                _ => basePower
            };

            if (power.IsZero())
            {
                return AttackPower.Zero;
            }

            var stateEffectPercentage = PercentageM.Hundred + buffPercentages.Sum() - debuffPercentages.Sum();

            var attackPercentage = powerParameter.Type == AttackPowerParameterType.Percentage
                ? powerParameter.ToPercentageM()
                : PercentageM.Hundred;

            return AttackPower.Max(power * stateEffectPercentage * attackPercentage, AttackPower.LowerLimitWithDebuff);
        }
    }
}
