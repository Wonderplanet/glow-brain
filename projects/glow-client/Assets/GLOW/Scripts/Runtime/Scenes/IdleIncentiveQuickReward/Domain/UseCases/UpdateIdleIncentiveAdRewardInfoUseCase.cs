using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.IdleIncentiveQuickReward.Domain.Moels;
using GLOW.Scenes.PassShop.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Domain.UseCases
{
    public class UpdateIdleIncentiveAdRewardInfoUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }
        
        public IdleIncentiveQuickRewardAdvertiseModel CalcReceivableTime()
        {
            var mstIdleIncentive = MstIdleIncentiveRepository.GetMstIdleIncentive();
            var usrIdleIncentive = GameRepository.GetGameFetchOther().UserIdleIncentiveModel;
            var nowTime = TimeProvider.Now;
            
            var adSkipPass = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();
            
            var remainingTimeSpan = CalcIdleIncentiveAdRewardReceivableTimeCalculator.CalcReceivableTime(
                mstIdleIncentive, 
                usrIdleIncentive, 
                nowTime);

            return new IdleIncentiveQuickRewardAdvertiseModel(
                new RemainingTimeSpan(remainingTimeSpan),
                adSkipPass);
        }
    }
}
