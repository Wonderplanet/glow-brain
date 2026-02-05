using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.SpecialAttackInfo.Presentation.ViewModels
{
    public record SpecialAttackInfoGradeViewModel(
        UnitGrade UnitGrade,
        SpecialAttackInfoGradeDescription GradeDescription);
}
