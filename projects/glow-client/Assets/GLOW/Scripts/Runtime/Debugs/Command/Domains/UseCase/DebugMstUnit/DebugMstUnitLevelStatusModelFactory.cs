using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Encoder;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public record DebugMstLevelStatusUseCaseModel(
        UnitLevel Level,
        UnitRank Rank,
        HP BaseHP,
        AttackPower BaseAttackPower,
        IReadOnlyList<DebugMstLevelStatusPerGradeStatusModel> PerGradeStatuses);

    public record DebugMstLevelStatusPerGradeStatusModel(UnitGrade Grade, HP Hp, AttackPower AttackPower);

    public record DebugMstLevelAndRankModel(UnitLevel Level, UnitRank Rank);

    public interface IDebugMstUnitLevelStatusModelFactory
    {
        IReadOnlyList<DebugMstLevelStatusUseCaseModel> Create(MstCharacterModel mstCharacterModel);
    }

    public class DebugMstUnitLevelStatusModelFactory : IDebugMstUnitLevelStatusModelFactory
    {
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitStatusCalculator UnitStatusCalculator { get; }
        [Inject] IUnitBaseStatusCalculator UnitBaseStatusCalculator { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] IMstUnitRankCoefficientRepository MstUnitRankCoefficientRepository { get; }
        [Inject] IMstUnitGradeCoefficientRepository MstUnitGradeCoefficientRepository { get; }

        IReadOnlyList<DebugMstLevelStatusUseCaseModel> IDebugMstUnitLevelStatusModelFactory.Create(MstCharacterModel mstCharacterModel)
        {
            return CreateDebugMstLevelStatusUseCaseModels(mstCharacterModel);
        }

        IReadOnlyList<DebugMstLevelStatusUseCaseModel> CreateDebugMstLevelStatusUseCaseModels(
            MstCharacterModel mstCharacterModel)
        {
            var maxLevel = MstUnitLevelUpRepository.GetUnitLevelUpList(mstCharacterModel.UnitLabel).Max(level => level.Level);
            var elements = new List<DebugMstLevelStatusUseCaseModel>();
            var targets = GetDebugMstLevelAndRankModels(mstCharacterModel);

            var mstUnitGrades = GetUnitGradesFromMst(mstCharacterModel);
            var statusExponent = GetStatusExponent(mstCharacterModel);

            foreach (var target in targets)
            {
                var baseHp = UnitBaseStatusCalculator.CalculateBaseStatus(
                    mstCharacterModel.MinHp.Value,
                    mstCharacterModel.MaxHp.Value,
                    target.Level.Value,
                    maxLevel.Value,
                    statusExponent.Value);

                var baseAttackPower = UnitBaseStatusCalculator.CalculateBaseStatus(
                    mstCharacterModel.MinAttackPower.Value,
                    mstCharacterModel.MaxAttackPower.Value,
                    target.Level.Value,
                    maxLevel.Value,
                    statusExponent.Value);

                var statusPerGrade = mstUnitGrades
                    .Select(g =>
                        CreateDebugMstLevelStatusPerGradeStatusModel(
                            mstCharacterModel,
                            target.Level,
                            target.Rank,
                            g
                        ))
                    .ToList();

                var model = new DebugMstLevelStatusUseCaseModel(
                    target.Level,
                    target.Rank,
                    new HP((int)baseHp),
                    new AttackPower(baseAttackPower),
                    statusPerGrade
                );
                elements.Add(model);
            }

            return elements;
        }

        IReadOnlyList<DebugMstLevelAndRankModel> GetDebugMstLevelAndRankModels(MstCharacterModel mstCharacterModel)
        {
            //RankによるレベルUp制限がある箇所は、LevelとRankの組み合わせで同一Levelでステータスが異なるケースがある
            //例...Level20:Rank0, Level20:Rank1 → 各々ステータスが異なる
            var maxLevel = MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).Value.ToUnitLevel();
            var result = new List<DebugMstLevelAndRankModel>();
            var specificRankUps = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstCharacterModel.Id);
            var normalUnitRankUps = MstUnitRankUpRepository.GetUnitRankUpList(mstCharacterModel.UnitLabel);
            for (var i = 1; i <= maxLevel.Value; i++)
            {
                result.Add(new DebugMstLevelAndRankModel(new UnitLevel(i), GetUnitRankFromLevel(mstCharacterModel, new UnitLevel(i))));

                //対象がRank上げないと上げられないレベルか？
                var isTargetRankUpLevel = specificRankUps.Any()
                    ? specificRankUps.Exists(s => s.RequireLevel.Value == i)
                    : normalUnitRankUps.Exists(s => s.RequireLevel.Value == i);
                if (isTargetRankUpLevel)
                {
                    var rank = GetUnitRankFromLevel(mstCharacterModel, new UnitLevel(i));
                    result.Add(new DebugMstLevelAndRankModel(new UnitLevel(i), rank + 1));
                }
            }

            return result;
        }

        // レベルからRankを計算
        // もしRankUp要求レベルだったら、小さいRankの方を返す
        UnitRank GetUnitRankFromLevel(MstCharacterModel mstCharacterModel, UnitLevel unitLevel)
        {
            var specificRankUps = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstCharacterModel.Id);
            var normalUnitRankUps = MstUnitRankUpRepository.GetUnitRankUpList(mstCharacterModel.UnitLabel);
            if (specificRankUps.Any())
            {
                return specificRankUps
                    .LastOrDefault(s => s.RequireLevel.Value < unitLevel.Value)
                    ?.Rank ?? UnitRank.Min;
            }
            else
            {
                return normalUnitRankUps
                    .LastOrDefault(s => s.RequireLevel.Value < unitLevel.Value)
                    ?.Rank ?? UnitRank.Min;
            }
        }

        IReadOnlyList<UnitGrade> GetUnitGradesFromMst(MstCharacterModel mstCharacterModel)
        {
            var msts = MstUnitGradeCoefficientRepository.GetUnitGradeCoefficientList()
                .Where(m => m.UnitLabel == mstCharacterModel.UnitLabel)
                .ToList();

            return msts
                .OrderBy(m => m.GradeLevel)
                .Select(m => m.GradeLevel)
                .ToList();
        }

        DebugMstLevelStatusPerGradeStatusModel CreateDebugMstLevelStatusPerGradeStatusModel(
            MstCharacterModel mstCharacterModel,
            UnitLevel unitLevel,
            UnitRank unitRank,
            UnitGrade unitGrade
        )
        {
            var maxLevel = MstUnitLevelUpRepository.GetUnitLevelUpList(mstCharacterModel.UnitLabel).Max(level => level.Level);
            var hp = UnitStatusCalculator.CalculateHp(
                unitLevel,
                mstCharacterModel.MinHp,
                mstCharacterModel.MaxHp,
                maxLevel,
                GetRequiredRankLevel(mstCharacterModel, unitRank),
                GetStatusExponent(mstCharacterModel),
                GetUnitGradeCoefficient(mstCharacterModel, unitGrade),
                GetUnitRankCoefficient(mstCharacterModel, unitRank));

            var attackPower = UnitStatusCalculator.CalculateAttackPower(
                unitLevel,
                mstCharacterModel.MinAttackPower,
                mstCharacterModel.MaxAttackPower,
                maxLevel,
                GetRequiredRankLevel(mstCharacterModel, unitRank),
                GetStatusExponent(mstCharacterModel),
                GetUnitGradeCoefficient(mstCharacterModel, unitGrade),
                GetUnitRankCoefficient(mstCharacterModel, unitRank));

            return new DebugMstLevelStatusPerGradeStatusModel(
                unitGrade,
                hp,
                attackPower);
        }

        UnitLevel GetRequiredRankLevel(MstCharacterModel mstCharacterModel, UnitRank unitRank)
        {
            var requiredRankLevel = UnitLevel.One;
            if (mstCharacterModel.HasSpecificRankUp)
            {
                var mstSpecificRankUp = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstCharacterModel.Id)
                    .FirstOrDefault(mst => mst.Rank == unitRank, MstUnitSpecificRankUpModel.Empty);
                if (!mstSpecificRankUp.IsEmpty()) requiredRankLevel = mstSpecificRankUp.RequireLevel;
            }
            else
            {
                var mstRankUp = MstUnitRankUpRepository.GetUnitRankUpList(mstCharacterModel.UnitLabel)
                    .FirstOrDefault(mst => mst.Rank == unitRank, MstUnitRankUpModel.Empty);
                if (!mstRankUp.IsEmpty()) requiredRankLevel = mstRankUp.RequireLevel;
            }

            return requiredRankLevel;
        }

        UnitStatusExponent GetStatusExponent(MstCharacterModel mstCharacterModel)
        {
            return mstCharacterModel.IsSpecialUnit
                ? MstConfigRepository.GetConfig(MstConfigKey.SpecialUnitStatusExponent).Value.ToUnitStatusExponent()
                : MstConfigRepository.GetConfig(MstConfigKey.UnitStatusExponent).Value.ToUnitStatusExponent();
        }

        UnitRankCoefficient GetUnitRankCoefficient(MstCharacterModel mstCharacterModel, UnitRank unitRank)
        {
            var rankCoefficient = UnitRankCoefficient.Empty;

            var mstUnitRankCoefs = MstUnitRankCoefficientRepository.GetUnitRankCoefficientList();
            var targetMstUnitRankCoefs = mstUnitRankCoefs.Find(coefficient => coefficient.Rank == unitRank);

            if (targetMstUnitRankCoefs != null)
            {
                rankCoefficient = mstCharacterModel.IsSpecialUnit
                    ? targetMstUnitRankCoefs.SpecialUnitCoefficient
                    : targetMstUnitRankCoefs.Coefficient;
            }

            return rankCoefficient;
        }

        UnitGradeCoefficient GetUnitGradeCoefficient(MstCharacterModel mstUnit, UnitGrade unitGrade)
        {
            var mstUnitGradeCoefs = MstUnitGradeCoefficientRepository.GetUnitGradeCoefficientList();
            var targetMstUnitGradeCoef = mstUnitGradeCoefs.Find(coefficient => coefficient.GradeLevel == unitGrade && coefficient.UnitLabel == mstUnit.UnitLabel);

            var gradeCoefficient = UnitGradeCoefficient.Empty;
            if (targetMstUnitGradeCoef != null && !mstUnit.IsSpecialUnit)
            {
                gradeCoefficient = targetMstUnitGradeCoef.Coefficient;
            }

            return gradeCoefficient;
        }
    }
}
