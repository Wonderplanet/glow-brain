using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Models
{
    public record MstSpecialAttackModel(
        MasterDataId MstAttackId,
        MasterDataId MstUnitId,
        UnitGrade UnitGrade,
        SpecialAttackName Name,
        SpecialAttackInfoDescription Description,
        SpecialAttackInfoGradeDescription GradeDescription,
        AttackData AttackData,
        IReadOnlyList<SpecialRoleLevelUpAttackElement> SpecialRoleLevelUpAttackElements)
    {
        public static MstSpecialAttackModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty, 
            UnitGrade.Empty, 
            SpecialAttackName.Empty, 
            SpecialAttackInfoDescription.Empty, 
            SpecialAttackInfoGradeDescription.Empty, 
            AttackData.Empty,
            System.Array.Empty<SpecialRoleLevelUpAttackElement>());
    }
}
