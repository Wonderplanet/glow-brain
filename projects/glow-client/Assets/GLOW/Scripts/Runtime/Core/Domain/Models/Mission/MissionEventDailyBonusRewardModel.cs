using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionEventDailyBonusRewardModel(
        MasterDataId MstMissionEventDailyBonusScheduleId,
        LoginDayCount LoginDayCount,
        RewardModel RewardModel)
    {
        public static MissionEventDailyBonusRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            LoginDayCount.Empty,
            RewardModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
