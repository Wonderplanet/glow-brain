using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Calculator
{
    public interface IUnitStatusCalculator
    {
        HP CalculateHp(
            UnitLevel unitLevel,
            HP minHp,
            HP maxHp,
            UnitLevel maxLevel,
            UnitLevel currentRankBaseLevel,
            UnitStatusExponent statusExponent,
            UnitGradeCoefficient gradeCoefficient,
            UnitRankCoefficient rankStatusCoefficient);

        AttackPower CalculateAttackPower(
            UnitLevel unitLevel,
            AttackPower minAttackPower,
            AttackPower maxAttackPower,
            UnitLevel maxLevel,
            UnitLevel currentRankBaseLevel,
            UnitStatusExponent statusExponent,
            UnitGradeCoefficient gradeCoefficient,
            UnitRankCoefficient rankStatusCoefficient);
    }
}
