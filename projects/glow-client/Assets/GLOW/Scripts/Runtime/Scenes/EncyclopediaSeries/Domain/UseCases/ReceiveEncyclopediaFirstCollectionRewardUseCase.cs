using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.EncyclopediaSeries.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.UseCases
{
    public class ReceiveEncyclopediaFirstCollectionRewardUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IEncyclopediaService EncyclopediaService { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public async UniTask<IReadOnlyList<CommonReceiveResourceModel>> ReceiveReward(CancellationToken cancellationToken, MasterDataId mstId, EncyclopediaType type)
        {
            var isNew = IsNewEncyclopediaContent(mstId, type);
            if (!isNew) return new List<CommonReceiveResourceModel>();

            var result = await EncyclopediaService.ReceiveFirstCollectionReward(cancellationToken, type, mstId);

            //副作用
            UpdateGameFetchOther(result);

            return CreateCommonReceiveModels(result.RewardModels);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModels(IReadOnlyList<RewardModel> models)
        {
            return models
                .Select(r =>
                    new CommonReceiveResourceModel(
                        r.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                        PlayerResourceModelFactory.Create(r.PreConversionResource)))
                .ToList();
        }

        bool IsNewEncyclopediaContent(MasterDataId mstId, EncyclopediaType type)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            bool isNew = false;
            switch (type)
            {
                case EncyclopediaType.Unit:
                    var userUnit = gameFetchOther.UserUnitModels
                        .FirstOrDefault(unit => unit.MstUnitId == mstId, UserUnitModel.Empty);
                    isNew = userUnit.IsNewEncyclopedia;
                    break;
                case EncyclopediaType.EnemyDiscovery:
                    var userEnemyDiscovery = gameFetchOther.UserEnemyDiscoverModels
                        .FirstOrDefault(discovery => discovery.MstEnemyCharacterId == mstId, UserEnemyDiscoverModel.Empty);
                    isNew = userEnemyDiscovery.IsNewEncyclopedia;
                    break;
                case EncyclopediaType.Artwork:
                    var userArtwork = gameFetchOther.UserArtworkModels
                        .FirstOrDefault(artwork => artwork.MstArtworkId == mstId, UserArtworkModel.Empty);
                    isNew = userArtwork.IsNewEncyclopedia;
                    break;
                case EncyclopediaType.Emblem:
                    var userEmblem = gameFetchOther.UserEmblemModel
                        .FirstOrDefault(emblem => emblem.MstEmblemId == mstId, UserEmblemModel.Empty);
                    isNew = userEmblem.IsNewEncyclopedia;
                    break;
            }

            return isNew;
        }

        void UpdateGameFetchOther(EncyclopediaReceiveFirstCollectionRewardResultModel model)
        {
            var gameFetch = GameRepository.GetGameFetch();
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var updatedGameFetch = gameFetch with
            {
                UserParameterModel = model.UserParameter,
            };
            var updatedGameFetchOther = gameFetchOther with
            {
                UserEmblemModel = gameFetchOther.UserEmblemModel.Update(model.UserEmblems),
                UserArtworkModels = gameFetchOther.UserArtworkModels.Update(model.UserArtworks),
                UserEnemyDiscoverModels = gameFetchOther.UserEnemyDiscoverModels.Update(model.UserEnemyDiscoveries),
                UserUnitModels = gameFetchOther.UserUnitModels.Update(model.UserUnits),
            };

            GameManagement.SaveGameUpdateAndFetch(
                updatedGameFetch,
                updatedGameFetchOther);
        }
    }
}
