using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Encoder;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public record DebugMstUnitSpecialUnitSpecialParamUseCaseModel(
        IReadOnlyList<DebugMstUnitSpecialUnitSpecialParamElementUseCaseModel> Elements)
    {
        public bool IsSpecialRole => Elements.Any();
    };

    public record DebugMstUnitSpecialUnitSpecialParamElementUseCaseModel(
        UnitGrade UnitGrade,
        UnitLevel UnitLevel,
        UnitRank UnitRank,
        SpecialAttackInfoDescription SpecialAttackInfoDescription);

    public interface IDebugMstUnitSpecialUnitSpecialParamModelFactory
    {
        DebugMstUnitSpecialUnitSpecialParamUseCaseModel Create(MstCharacterModel mstCharacterModel);
    }

    public class DebugMstUnitSpecialUnitSpecialParamModelFactory
        : IDebugMstUnitSpecialUnitSpecialParamModelFactory
    {
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] IMstUnitGradeCoefficientRepository MstUnitGradeCoefficientRepository { get; }
        [Inject] ISpecialRoleSpecialAttackFactory SpecialRoleSpecialAttackFactory { get; }
        [Inject] ISpecialAttackDescriptionEncoder SpecialAttackDescriptionEncoder { get; }

        DebugMstUnitSpecialUnitSpecialParamUseCaseModel
            IDebugMstUnitSpecialUnitSpecialParamModelFactory.Create(MstCharacterModel mstCharacterModel)
        {
            if (mstCharacterModel.RoleType != CharacterUnitRoleType.Special)
            {
                return new DebugMstUnitSpecialUnitSpecialParamUseCaseModel(
                    new List<DebugMstUnitSpecialUnitSpecialParamElementUseCaseModel>());
            }

            var maxLevelModel = MstUnitLevelUpRepository.GetUnitMaxLevelUp(mstCharacterModel.UnitLabel);
            var mstUnitGrades = GetUnitGradesFromMst(mstCharacterModel);

            //foreachでlv1~80, grade1~5までを出す
            var gradeAndLevels = GetDebugMstLevelAndRankModels(mstCharacterModel);
            var elements = gradeAndLevels
                .SelectMany(gAndL =>
                {
                    return mstUnitGrades.Select(g =>
                    {
                        var currentSpecialAttack = mstCharacterModel.GetSpecialAttack(g);
                        var description = GetDescription(
                            currentSpecialAttack,
                            gAndL.Level,
                            maxLevelModel.Level);
                        return new DebugMstUnitSpecialUnitSpecialParamElementUseCaseModel(
                            g,
                            gAndL.Level,
                            gAndL.Rank,
                            description
                        );
                    });
                })
                .OrderBy(result => result.UnitGrade.Value)
                .ThenBy(result => result.UnitLevel.Value)
                .ToList();
            return new DebugMstUnitSpecialUnitSpecialParamUseCaseModel(elements);
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

        // SpecialAttackInfoModelFactoryのコード引用
        SpecialAttackInfoDescription GetDescription(
            MstSpecialAttackModel mstSpecialAttack,
            UnitLevel unitLevel,
            UnitLevel maxUnitLevel)
        {
            var specialAttackData = SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(
                mstSpecialAttack,
                unitLevel,
                maxUnitLevel);

            var description = SpecialAttackDescriptionEncoder.DescriptionEncode(
                mstSpecialAttack.Description,
                specialAttackData.AttackElements);

            return description;
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
    }
}
