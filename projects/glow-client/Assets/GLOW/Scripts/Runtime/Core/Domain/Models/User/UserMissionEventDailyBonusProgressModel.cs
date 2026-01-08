using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record UserMissionEventDailyBonusProgressModel(
        MasterDataId MstMissionEventDailyBonusScheduleId,
        LoginDayCount ProgressLoginDayCount)
    {
        public static UserMissionEventDailyBonusProgressModel Empty { get; } = new(
            MasterDataId.Empty,
            LoginDayCount.Empty
        );
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public MasterDataId MstMissionEventDailyBonusScheduleId { get; } = MstMissionEventDailyBonusScheduleId;

        public LoginDayCount ProgressLoginDayCount { get; } = ProgressLoginDayCount;
    }
}
