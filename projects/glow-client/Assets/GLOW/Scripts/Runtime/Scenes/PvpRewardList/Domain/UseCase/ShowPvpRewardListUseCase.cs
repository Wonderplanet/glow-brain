using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpRewardList.Domain.Factory;
using GLOW.Scenes.PvpRewardList.Domain.Model;
using Zenject;

namespace GLOW.Scenes.PvpRewardList.Domain.UseCase
{
    public class ShowPvpRewardListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPvpRewardModelFactory PvpRewardModelFactory { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        public PvpRewardListModel FetchPvpRewardListModel()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var sysPvpSeasonId = gameFetchOther.SysPvpSeasonModel.Id;
            var rankingRewards = PvpRewardModelFactory.CreateRankingRewardModels(sysPvpSeasonId);
            var pointRankRewards = PvpRewardModelFactory.CreatePvpPointRankRewardModels(sysPvpSeasonId);
            var totalScoreRewards = PvpRewardModelFactory.CreatePvpTotalScoreRewardModels(sysPvpSeasonId);
            
            var nextWeeklyResetTime = DailyResetTimeCalculator.GetRemainingTimeToWeeklyReset();
            return new PvpRewardListModel(
                new RemainingTimeSpan(nextWeeklyResetTime),
                rankingRewards,
                pointRankRewards,
                totalScoreRewards);
        }
    }
}