using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models.ComebackDailyBonus
{
    public record ComebackBonusRewardModel(
        MasterDataId MstComebackBonusScheduleId,
        LoginDayCount LoginDayCount,
        RewardModel Reward)
    {
        public static ComebackBonusRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            LoginDayCount.Empty,
            RewardModel.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}