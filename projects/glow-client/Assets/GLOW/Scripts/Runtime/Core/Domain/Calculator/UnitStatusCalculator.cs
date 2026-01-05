using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using UnityEngine;

namespace GLOW.Core.Domain.Calculator
{
    public class UnitStatusCalculator : IUnitStatusCalculator, IUnitBaseStatusCalculator
    {
        HP IUnitStatusCalculator.CalculateHp(
            UnitLevel unitLevel,
            HP minHp,
            HP maxHp,
            UnitLevel maxLevel,
            UnitLevel currentRankBaseLevel,
            UnitStatusExponent statusExponent,
            UnitGradeCoefficient gradeCoefficient,
            UnitRankCoefficient rankStatusCoefficient)
        {

            var hp = CalculateStatus(
                unitLevel,
                minHp.Value,
                maxHp.Value,
                maxLevel,
                statusExponent,
                currentRankBaseLevel,
                gradeCoefficient,
                rankStatusCoefficient);

            return new HP(decimal.ToInt32(hp));
        }

        AttackPower IUnitStatusCalculator.CalculateAttackPower(
            UnitLevel unitLevel,
            AttackPower minAttackPower,
            AttackPower maxAttackPower,
            UnitLevel maxLevel,
            UnitLevel currentRankBaseLevel,
            UnitStatusExponent statusExponent,
            UnitGradeCoefficient gradeCoefficient,
            UnitRankCoefficient rankStatusCoefficient)
        {
            var attackPower = CalculateStatus(
                unitLevel,
                minAttackPower.Value,
                maxAttackPower.Value,
                maxLevel,
                statusExponent,
                currentRankBaseLevel,
                gradeCoefficient,
                rankStatusCoefficient);

            return new AttackPower(attackPower);
        }

        static decimal CalculateStatus(
            UnitLevel unitLevel,
            decimal minStatus,
            decimal maxStatus,
            UnitLevel maxLevel,
            UnitStatusExponent statusExponent,
            UnitLevel currentRankBaseLevel,
            UnitGradeCoefficient gradeCoefficient,
            UnitRankCoefficient rankStatusCoefficient)
        {
            var baseStatus = CalculateBaseStatus(minStatus, maxStatus, unitLevel.Value, maxLevel.Value, statusExponent.Value);

            var rankBonusStatus = 0m;
            if (!rankStatusCoefficient.IsEmpty())
            {
                var rankReferenceHP = CalculateBaseStatus(minStatus, maxStatus, currentRankBaseLevel.Value, maxLevel.Value, statusExponent.Value);
                rankBonusStatus = CalculateRankStatus(rankReferenceHP, rankStatusCoefficient.Value);
            }

            decimal gradeBonusStatus = 0;
            if (!gradeCoefficient.IsEmpty())
            {
                gradeBonusStatus = CalculateGradeStatus(baseStatus, gradeCoefficient.Value);
            }

            return baseStatus + rankBonusStatus + gradeBonusStatus;
        }

        decimal IUnitBaseStatusCalculator.CalculateBaseStatus(decimal minStatus, decimal maxStatus, decimal currentLevel, decimal maxLevel, decimal exponent)
        {
            return CalculateBaseStatus(minStatus, maxStatus, currentLevel, maxLevel, exponent);
        }

        static decimal CalculateBaseStatus(decimal minStatus, decimal maxStatus, decimal currentLevel, decimal maxLevel, decimal exponent)
        {
            // 基本ステータス: Lv1時の値 + (最大Lv時の値-Lv1時の値) * ((現在Lv-1)/(最大Lv-1) ^ 指数)
            var v = Math.Pow((double)((currentLevel - 1) / (maxLevel - 1)), (double)exponent);
            return decimal.Floor(minStatus + (maxStatus - minStatus) * Convert.ToDecimal(v));
        }

        static decimal CalculateRankStatus(decimal rankReferenceStatus, decimal rankStatusCoefficient)
        {
            // ランク補正: (ランク参照Lvの基礎ステータス*N%)
            return decimal.Floor(rankReferenceStatus * rankStatusCoefficient * 0.01m);
        }

        static decimal CalculateGradeStatus(decimal baseStatus, int gradeCoefficient)
        {
            return decimal.Floor(baseStatus * gradeCoefficient * 0.01m);
        }
    }
}
