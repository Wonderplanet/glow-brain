using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.EncyclopediaReward.Domain.Models;
using UnityEngine.PlayerLoop;
using Zenject;

namespace GLOW.Scenes.EncyclopediaReward.Domain.UseCases
{
    public class ReceiveUnitEncyclopediaRewardUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IEncyclopediaService EncyclopediaService { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public async UniTask<IReadOnlyList<CommonReceiveResourceModel>> ReceiveRewards(
            CancellationToken cancellationToken,
            IReadOnlyList<MasterDataId> mstUnitEncyclopediaRewardIds)
        {
            var result = await EncyclopediaService.ReceiveReward(cancellationToken, mstUnitEncyclopediaRewardIds);
            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;

            // 副作用
            UpdateGameModel(result);

            //副作用
            UserLevelUpCacheRepository.Save(
                result.UserLevelUp,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            return CreateCommonReceiveModel(result.UnitEncyclopediaRewards);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModel(IReadOnlyList<RewardModel> models)
        {
            return models
                .Select(r =>
                    new CommonReceiveResourceModel(
                        r.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                        PlayerResourceModelFactory.Create(r.PreConversionResource)))
                .ToList();
        }


        void UpdateGameModel(EncyclopediaReceiveRewardResultModel receiveRewardResultModel)
        {
            var gameFetch = GameRepository.GetGameFetch();
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetch = gameFetch with
            {
                UserParameterModel = receiveRewardResultModel.UserParameter,
            };

            var newGameFetchOther = gameFetchOther with
            {
                UserReceivedUnitEncyclopediaRewardModels = gameFetchOther.UserReceivedUnitEncyclopediaRewardModels
                    .Update(receiveRewardResultModel.UserReceivedUnitEncyclopediaRewards),
                UserConditionPackModels = gameFetchOther.UserConditionPackModels
                    .Update(receiveRewardResultModel.UserConditionPacks),
                UserItemModels = gameFetchOther.UserItemModels
                    .Update(receiveRewardResultModel.UserItems),
            };

            GameManagement.SaveGameUpdateAndFetch(newGameFetch, newGameFetchOther);
        }
    }
}
