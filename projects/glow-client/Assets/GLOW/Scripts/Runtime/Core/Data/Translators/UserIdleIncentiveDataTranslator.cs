using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;

namespace GLOW.Core.Data.Translators
{
    public static class UserIdleIncentiveDataTranslator
    {
        public static UserIdleIncentiveModel ToModel(UsrIdleIncentiveData data)
        {
            return new UserIdleIncentiveModel(
                data.IdleStartedAt,
                new IdleIncentiveReceiveCount(data.DiamondQuickReceiveCount),
                new IdleIncentiveReceiveCount(data.AdQuickReceiveCount),
                data.DiamondQuickReceiveAt,
                data.AdQuickReceiveAt);

        }
    }
}
