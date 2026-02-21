using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Data.Translators
{
    public class SpecialAttackDataTranslator
    {
        public static MstSpecialAttackModel ToSpecialAttackModel(
            MstAttackData mstAttackData,
            MstAttackI18nData mstAttackI18nData,
            MstSpecialAttackI18nData mstSpecialAttackI18nData,
            AttackData attackData,
            IReadOnlyList<SpecialRoleLevelUpAttackElement> specialRoleLevelUpAttackElements)

        {
            var gradeDescription = string.IsNullOrEmpty(mstAttackI18nData.GradeDescription)
                ? SpecialAttackInfoGradeDescription.Empty
                : new SpecialAttackInfoGradeDescription(mstAttackI18nData.GradeDescription);
            
            return new MstSpecialAttackModel(
                new MasterDataId(mstAttackData.Id),
                new MasterDataId(mstAttackData.MstUnitId),
                new UnitGrade(mstAttackData.UnitGrade),
                new SpecialAttackName(mstSpecialAttackI18nData.Name),
                new SpecialAttackInfoDescription(mstAttackI18nData.Description),
                gradeDescription,
                attackData,
                specialRoleLevelUpAttackElements);
        }
    }
}
