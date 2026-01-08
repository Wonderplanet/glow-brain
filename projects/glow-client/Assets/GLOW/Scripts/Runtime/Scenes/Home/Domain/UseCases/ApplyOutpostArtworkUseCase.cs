using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.OutpostEnhance.Domain.Definitions.Services;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class ApplyOutpostArtworkUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }
        [Inject] IOutpostService OutpostService { get; }
        [Inject] IGameManagement GameManagement { get; }

        public bool IsNeedApplyOutpostArtwork()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userOutpost = gameFetchOther.UserOutpostModels.First(outpost => outpost.IsUsed);
            var selectedMstArtworkId = OutpostArtworkCacheRepository.GetSelectedArtwork();
            return userOutpost.MstArtworkId != selectedMstArtworkId;
        }

        public async UniTask ChangeArtwork(CancellationToken cancellationToken)
        {
            var mstArtworkId = OutpostArtworkCacheRepository.GetSelectedArtwork();
            var outpost = GameRepository.GetGameFetchOther().UserOutpostModels.Find(model => model.IsUsed);
            var result = await OutpostService.ChangeArtwork(cancellationToken, outpost.MstOutpostId, mstArtworkId);
            UpdateGameModel(result.UserOutpostModel);
        }

        void UpdateGameModel(UserHomeOutpostModel userOutpostModel)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetchOther = gameFetchOther with
            {
                UserOutpostModels = gameFetchOther.UserOutpostModels.Update(userOutpostModel)
            };

            GameManagement.SaveGameFetchOther(newGameFetchOther);
        }
    }
}
