using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;

namespace GLOW.Core.Domain.Models
{
    public record MstIdleIncentiveModel(
        MasterDataId Id,
        TimeSpan InitialRewardReceiveMinutes,
        TimeSpan MaxIdleHours,
        IdleIncentiveReceiveCount MaxDailyDiamondQuickReceiveAmount,
        ObscuredPlayerResourceAmount RequiredQuickReceiveDiamondAmount,
        IdleIncentiveReceiveCount MaxDailyAdQuickReceiveAmount,
        TimeSpan AdIntervalSeconds,
        TimeSpan QuickIdleMinutes,
        TimeSpan RewardIncreaseIntervalMinutes)
    {
        public static MstIdleIncentiveModel Empty { get; } = new(
            MasterDataId.Empty,
            TimeSpan.Zero,
            TimeSpan.Zero,
            IdleIncentiveReceiveCount.Empty,
            ObscuredPlayerResourceAmount.Empty,
            IdleIncentiveReceiveCount.Empty,
            TimeSpan.Zero,
            TimeSpan.Zero,
            TimeSpan.Zero);
    }
}
