using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ComebackDailyBonus;

namespace GLOW.Core.Domain.Models
{
    public record MstComebackBonusScheduleModel(
        MasterDataId MstComebackBonusScheduleId,
        InactiveConditionDays InactiveConditionDays,
        ComebackDailyBonusDurationDays DurationDays,
        ComebackDailyBonusStartAt StartAt,
        ComebackDailyBonusEndAt EndAt)
    {
        public static MstComebackBonusScheduleModel Empty { get; } = new(
            MasterDataId.Empty,
            InactiveConditionDays.Empty,
            ComebackDailyBonusDurationDays.Empty,
            ComebackDailyBonusStartAt.Empty,
            ComebackDailyBonusEndAt.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}