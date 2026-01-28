using System;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record UserIdleIncentiveModel(
        ObscuredDateTimeOffset IdleStartedAt,
        IdleIncentiveReceiveCount DiamondQuickReceiveCount,
        IdleIncentiveReceiveCount AdQuickReceiveCount,
        ObscuredDateTimeOffset DiamondQuickReceiveAt,
        ObscuredDateTimeOffset AdQuickReceiveAt)
    {
        public static UserIdleIncentiveModel Empty => new UserIdleIncentiveModel(
            DateTimeOffset.MinValue,
            IdleIncentiveReceiveCount.Empty,
            IdleIncentiveReceiveCount.Empty,
            DateTimeOffset.MinValue,
            DateTimeOffset.MinValue
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
