using System;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.UseCase
{
    public class GetIdleIncentiveElapsedTimeUseCase
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }

        public TimeSpan GetIdleIncentiveElapsedTimeSpan()
        {
            var mstIdleIncentive = MstIdleIncentiveRepository.GetMstIdleIncentive();

            var idleStartedAt = GameRepository.GetGameFetchOther().UserIdleIncentiveModel.IdleStartedAt;
            var elapsed = TimeProvider.Now - idleStartedAt;
            
            if (elapsed < TimeSpan.Zero)
            {
                elapsed = TimeSpan.Zero;
            }
            
            if (elapsed >= mstIdleIncentive.MaxIdleHours)
            {
                elapsed = mstIdleIncentive.MaxIdleHours;
            }
            return elapsed;
        }
    }
}
