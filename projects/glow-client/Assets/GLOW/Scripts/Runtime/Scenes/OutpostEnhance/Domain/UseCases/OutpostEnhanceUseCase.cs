using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Outpost;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.OutpostEnhance.Domain.Definitions.Services;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Domain.UseCases
{
    public class OutpostEnhanceUseCase
    {
        [Inject] IOutpostService OutpostService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public async UniTask<OutpostEnhanceResultModel> OutpostEnhance(CancellationToken cancellationToken,MasterDataId outpostId, MasterDataId enhanceId, OutpostEnhanceLevel nextLevel)
        {
            var result = await OutpostService.EnhanceOutpost(cancellationToken, enhanceId, nextLevel);

            var gameFetch = GameRepository.GetGameFetch();
            var gameFetchOther = GameRepository.GetGameFetchOther();
            
            var updatedUserOutpostEnhanceModel = new UserOutpostEnhanceModel(
                outpostId,
                enhanceId,
                result.UserOutpostEnhanceLevelResultModel.AfterLevel);

            var newGameFetchOther = gameFetchOther with
            {
                UserOutpostEnhanceModels = gameFetchOther.UserOutpostEnhanceModels.Update(updatedUserOutpostEnhanceModel)
            };

            var newGameFetch = gameFetch with
            {
                UserParameterModel = result.UserParameterModel
            };

            GameManagement.SaveGameUpdateAndFetch(newGameFetch, newGameFetchOther);

            return result;
        }
    }
}
