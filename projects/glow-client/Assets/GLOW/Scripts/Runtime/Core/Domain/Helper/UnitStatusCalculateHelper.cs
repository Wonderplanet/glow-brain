using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using Zenject;

namespace GLOW.Core.Domain.Helper
{
    public class UnitStatusCalculateHelper : IUnitStatusCalculateHelper
    {
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] IMstUnitRankCoefficientRepository MstUnitRankCoefficientRepository { get; }
        [Inject] IMstUnitGradeCoefficientRepository MstUnitGradeCoefficientRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitStatusCalculator UnitStatusCalculator { get; }
        [Inject] IInGameSpecialRuleUnitStatusProvider InGameSpecialRuleUnitStatusProvider { get; }

        public UnitCalculateStatusModel Calculate(
            MstCharacterModel mstUnit,
            UnitLevel unitLevel,
            UnitRank unitRank,
            UnitGrade unitGrade)
        {
            var maxLevel = MstUnitLevelUpRepository.GetUnitLevelUpList(mstUnit.UnitLabel).Max(level => level.Level);
            var mstRankCoefficient = GetUnitRankCoefficient(mstUnit, unitRank);
            var mstGradeCoefficient = GetUnitGradeCoefficient(mstUnit, unitGrade);
            var statusExponent = mstUnit.IsSpecialUnit
                ? MstConfigRepository.GetConfig(MstConfigKey.SpecialUnitStatusExponent).Value.ToUnitStatusExponent()
                : MstConfigRepository.GetConfig(MstConfigKey.UnitStatusExponent).Value.ToUnitStatusExponent();

            var requiredRankLevel = UnitLevel.Empty;
            if (mstUnit.HasSpecificRankUp)
            {
                var mstSpecificRankUp = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstUnit.Id)
                    .FirstOrDefault(mst => mst.Rank == unitRank, MstUnitSpecificRankUpModel.Empty);
                requiredRankLevel = mstSpecificRankUp.RequireLevel;
            }
            else
            {
                var mstRankUp = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel)
                    .FirstOrDefault(mst => mst.Rank == unitRank, MstUnitRankUpModel.Empty);
                requiredRankLevel = mstRankUp.RequireLevel;
            }

            var rankCoefficient = UnitRankCoefficient.Empty;
            if(mstRankCoefficient != null)
            {
                rankCoefficient = mstUnit.IsSpecialUnit
                    ? mstRankCoefficient.SpecialUnitCoefficient
                    : mstRankCoefficient.Coefficient;
            }
            var gradeCoefficient = UnitGradeCoefficient.Empty;
            if(mstGradeCoefficient != null && !mstUnit.IsSpecialUnit)
            {
                gradeCoefficient = mstGradeCoefficient.Coefficient;
            }

            var hp = UnitStatusCalculator.CalculateHp(
                unitLevel,
                mstUnit.MinHp,
                mstUnit.MaxHp,
                maxLevel,
                requiredRankLevel,
                statusExponent,
                gradeCoefficient,
                rankCoefficient);

            var attackPower = UnitStatusCalculator.CalculateAttackPower(
                unitLevel,
                mstUnit.MinAttackPower,
                mstUnit.MaxAttackPower,
                maxLevel,
                requiredRankLevel,
                statusExponent,
                gradeCoefficient,
                rankCoefficient);

            return new UnitCalculateStatusModel(hp, attackPower);
        }

        public UnitCalculateStatusModel CalculateStatusWithSpecialRule(
            UnitCalculateStatusModel calculatedStatus,
            MasterDataId unitId,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var specialRuleUnitStatusParameter = InGameSpecialRuleUnitStatusProvider
                .GetSpecialRuleUnitStatus(
                    unitId,
                    specialRuleUnitStatusModels);

            // HP計算は浮動小数点数で行い、最後に切り上げ
            var hpFloat = calculatedStatus.HP.Value *
                (float)specialRuleUnitStatusParameter.specialRuleHpPercentageM.Value / 100f;

            var hp = new HP((int)Math.Ceiling(hpFloat));

            var attackPower = calculatedStatus.AttackPower *
                              specialRuleUnitStatusParameter.specialRuleAttackPercentageM.ToRate();

            return new UnitCalculateStatusModel(hp, attackPower);
        }

        MstUnitRankCoefficientModel GetUnitRankCoefficient(MstCharacterModel mstUnit, UnitRank unitRank)
        {
            var rankCoefficients = MstUnitRankCoefficientRepository.GetUnitRankCoefficientList();
            return rankCoefficients.Find(coefficient => coefficient.Rank == unitRank);
        }

        MstUnitGradeCoefficientModel GetUnitGradeCoefficient(MstCharacterModel mstUnit, UnitGrade unitGrade)
        {
            var gradeCoefficients = MstUnitGradeCoefficientRepository.GetUnitGradeCoefficientList();
            return gradeCoefficients.Find(coefficient => coefficient.GradeLevel == unitGrade && coefficient.UnitLabel == mstUnit.UnitLabel);
        }
    }
}
