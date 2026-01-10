using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitTab.Domain.UseCase
{
    public class GetEncyclopediaNoticeUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstUnitEncyclopediaRewardDataRepository MstUnitEncyclopediaRewardDataRepository { get; }

        public NotificationBadge GetEncyclopediaNotification()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var unitGrade = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaGrade(gameFetchOther.UserUnitModels);
            var userReceivedRewards = gameFetchOther.UserReceivedUnitEncyclopediaRewardModels;
            var mstRewards = MstUnitEncyclopediaRewardDataRepository.GetUnitEncyclopediaRewards();
            var isUnReceived = mstRewards
                .Where(mst => mst.UnitEncyclopediaRank.Value <= unitGrade.Value)
                .Any(mst => userReceivedRewards.All(receivedReward => receivedReward.MstUnitEncyclopediaRewardId != mst.Id));

            return new NotificationBadge(isUnReceived);
        }
    }
}
