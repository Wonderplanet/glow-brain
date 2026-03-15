using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCase
{
    public class ArtworkGradeUpUseCase
    {
        [Inject] IEncyclopediaService EncyclopediaService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public async UniTask UpdateAndArtworkGradeUp(
            CancellationToken cancellationToken,
            MasterDataId mstArtworkId)
        {
            var result = await EncyclopediaService.ArtworkGradeUp(cancellationToken, mstArtworkId);
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();

            UpdateAndSaveRepository(gameFetchOtherModel, result);
        }

        void UpdateAndSaveRepository(
            GameFetchOtherModel gameFetchOtherModel,
            ArtworkGradeUpRewardResultModel resultModel)
        {
            var updateFetchOtherModel = gameFetchOtherModel with
            {
                UserArtworkModels = gameFetchOtherModel.UserArtworkModels.Update(new[] { resultModel.UserArtwork }),
                UserItemModels = gameFetchOtherModel.UserItemModels.Update(resultModel.UserItems)
            };

            GameManagement.SaveGameFetchOther(updateFetchOtherModel);
        }
    }
}
