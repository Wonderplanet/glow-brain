using System;
using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Domain.UseCases
{
    public static class CalcIdleIncentiveAdRewardReceivableTimeCalculator
    {
        public static TimeSpan CalcReceivableTime(MstIdleIncentiveModel mstIdleIncentive,UserIdleIncentiveModel usrIdleIncentive,DateTimeOffset now)
        {
            var adReceiveEnableTime = usrIdleIncentive.AdQuickReceiveAt + mstIdleIncentive.AdIntervalSeconds;
            return adReceiveEnableTime > now ? adReceiveEnableTime - now : TimeSpan.Zero;
        }
    }
}
