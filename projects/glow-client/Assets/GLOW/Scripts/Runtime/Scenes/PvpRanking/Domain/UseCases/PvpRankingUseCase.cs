using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Services;
using GLOW.Scenes.PvpRanking.Domain.ModelFactories;
using GLOW.Scenes.PvpRanking.Domain.Models;
using Zenject;
namespace GLOW.Scenes.PvpRanking.Domain.UseCases
{
    public class PvpRankingUseCase
    {
        [Inject] IPvpRankingModelFactory PvpRankingModelFactory { get; }
        [Inject] IPvpService PvpService { get; }

        public async UniTask<PvpRankingUseCaseModel> GetPvpRanking(CancellationToken cancellationToken)
        {
            var currentRankingTask = PvpService.Ranking(cancellationToken, false);
            var prevRankingTask = PvpService.Ranking(cancellationToken, true);

            (var currentRanking, var prevRanking) = await UniTask.WhenAll(
                currentRankingTask,
                prevRankingTask);
            var currentRankingModel = PvpRankingModelFactory.CreatePvpRankingElementUseCaseModel(
                currentRanking,
                false);
            var prevRankingModel = PvpRankingModelFactory.CreatePvpRankingElementUseCaseModel(
                prevRanking,
                true);

            return new PvpRankingUseCaseModel(currentRankingModel, prevRankingModel);
        }
    }
}
