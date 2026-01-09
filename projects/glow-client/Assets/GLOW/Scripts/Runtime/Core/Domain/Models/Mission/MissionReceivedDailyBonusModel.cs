using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionReceivedDailyBonusModel(
        MissionDailyBonusType MissionType,
        LoginDayCount LoginDayCount,
        RewardModel RewardModel)
    {
        public static MissionReceivedDailyBonusModel Empty { get; } = new(
            MissionDailyBonusType.DailyBonus,
            LoginDayCount.Empty,
            RewardModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
