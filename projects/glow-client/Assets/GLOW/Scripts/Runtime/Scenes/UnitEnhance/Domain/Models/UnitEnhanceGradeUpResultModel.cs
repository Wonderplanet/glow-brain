using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceGradeUpResultModel(UserDataId UserUnitId, UnitGrade BeforeGrade, UnitGrade AfterGrade);
}
