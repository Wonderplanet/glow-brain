using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Unit
{
    public record SpecialAttackInfoGradeModel(
        UnitGrade UnitGrade,
        SpecialAttackInfoGradeDescription GradeDescription);
}
