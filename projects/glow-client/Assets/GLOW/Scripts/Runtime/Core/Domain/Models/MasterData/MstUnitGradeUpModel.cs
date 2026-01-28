using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitGradeUpModel(UnitGrade GradeLevel, UnitLabel UnitLabel, ItemAmount RequireAmount)
    {
        public static MstUnitGradeUpModel Empty { get; } = new(UnitGrade.Empty, UnitLabel.DropR, ItemAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
