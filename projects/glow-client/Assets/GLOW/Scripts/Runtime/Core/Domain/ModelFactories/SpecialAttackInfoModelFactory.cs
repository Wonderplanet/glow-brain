using System.Collections.Generic;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.Encoder;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Core.Domain.ModelFactories
{
    public class SpecialAttackInfoModelFactory : ISpecialAttackInfoModelFactory
    {
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] ISpecialAttackDescriptionEncoder SpecialAttackDescriptionEncoder { get; }
        [Inject] ISpecialRoleSpecialAttackFactory SpecialRoleSpecialAttackFactory { get; }

        public SpecialAttackInfoModel Create(MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            var maxLevelModel = MstUnitLevelUpRepository.GetUnitMaxLevelUp(mstUnit.UnitLabel);
            
            return Create(mstUnit, userUnit.Grade, userUnit.Level, maxLevelModel.Level);
        }
        
        public SpecialAttackInfoModel Create(MstCharacterModel mstUnit, UnitGrade unitGrade, UnitLevel unitLevel)
        {
            var maxLevelModel = MstUnitLevelUpRepository.GetUnitMaxLevelUp(mstUnit.UnitLabel);
            
            return Create(mstUnit, unitGrade, unitLevel, maxLevelModel.Level);
        }

        SpecialAttackInfoModel Create(
            MstCharacterModel mstUnit, 
            UnitGrade unitGrade, 
            UnitLevel unitLevel, 
            UnitLevel maxUnitLevel)
        {
            var currentSpecialAttack = mstUnit.GetSpecialAttack(unitGrade);
            var description = GetDescription(currentSpecialAttack, unitLevel, maxUnitLevel);
            var gradeModels = CreateSpecialAttackInfoGradeModels(mstUnit);

            var specialAttackInfoModel = new SpecialAttackInfoModel(
                currentSpecialAttack.Name,
                description,
                mstUnit.SpecialAttackInitialCoolTime.ToSpecialAttackCoolTime(),
                gradeModels);

            return specialAttackInfoModel;
        }
        
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

        List<SpecialAttackInfoGradeModel> CreateSpecialAttackInfoGradeModels(MstCharacterModel mstUnit)
        {
            var gradeModels = mstUnit.SpecialAttacks
                .Where(s => !s.GradeDescription.IsEmpty())
                .Select(s => new SpecialAttackInfoGradeModel(s.UnitGrade, s.GradeDescription))
                .ToList();
            
            return gradeModels;
        }
    }
}

