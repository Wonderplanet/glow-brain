using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Domain.Moels
{
    public record IdleIncentiveQuickRewardAdvertiseModel(
        RemainingTimeSpan RemainingTimeAtReceivable,
        HeldAdSkipPassInfoModel HeldAdSkipPassInfoModel)
    {
        public static IdleIncentiveQuickRewardAdvertiseModel Empty { get; } = 
            new IdleIncentiveQuickRewardAdvertiseModel(
                RemainingTimeSpan.Empty,
                HeldAdSkipPassInfoModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}