using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ComebackDailyBonus;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record UserComebackBonusProgressModel(
        MasterDataId MstComebackBonusScheduleId,
        LoginDayCount ProgressLoginDayCount,
        ComebackDailyBonusStartAt StartAt,
        ComebackDailyBonusEndAt EndAt)
    {
        public static UserComebackBonusProgressModel Empty { get; } = new(
            MasterDataId.Empty,
            LoginDayCount.Empty,
            ComebackDailyBonusStartAt.Empty,
            ComebackDailyBonusEndAt.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}