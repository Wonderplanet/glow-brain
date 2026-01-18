using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Scenes.Home.Domain.Models;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class FetchHomeDataUseCase
    {
        [Inject] IGameService GameService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IHomeMainBadgeFactory HomeMainBadgeFactory { get; }

        public async UniTask<HomeMainBadgeModel> UpdateHomeBadgeAndMaintenance(CancellationToken cancellationToken)
        {
            var result = await GameService.Badge(cancellationToken);

            // バッジ
            var fetchModel = GameRepository.GetGameFetch();
            var updatedFetchModel = fetchModel with
            {
                BadgeModel = result.Badge
            };

            // 部分メンテ
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            var updatedFetchOtherModel = fetchOtherModel with
            {
                MngContentCloseModels = result.MngContentCloses
            };

            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);

            return HomeMainBadgeFactory.GetHomeMainBadgeModel();
        }
    }
}
