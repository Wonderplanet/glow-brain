using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitGradeCoefficientModel(UnitGrade GradeLevel, UnitLabel UnitLabel, UnitGradeCoefficient Coefficient);
}
