using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.Sorter;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.UseCase
{
    public class ReceiveIdleIncentiveRewardUseCase
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IIdleIncentiveService IdleIncentiveService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IPlayerResourceSorter PlayerResourceSorter { get; }

        public async UniTask<IReadOnlyList<CommonReceiveResourceModel>> ReceiveIdleIncentiveReward(CancellationToken cancellationToken)
        {
            var result = await IdleIncentiveService.Receive(cancellationToken);

            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;

            UpdateGameModel(
                result.UsrParameter,
                result.UsrItems,
                result.UserConditionPacks,
                result.UserIdleIncentive);

            UserLevelUpCacheRepository.Save(
                result.UserLevel,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            return CreateCommonReceiveModels(result.Rewards);
        }

        public async UniTask<IReadOnlyList<CommonReceiveResourceModel>> ReceiveQuickRewardByDiamond(CancellationToken cancellationToken)
        {
            var result = await IdleIncentiveService.QuickReceiveByItem(cancellationToken);

            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;

            UpdateGameModel(
                result.UsrParameter,
                result.UsrItems,
                result.UserConditionPacks,
                result.UserIdleIncentive);

            UserLevelUpCacheRepository.Save(
                result.UserLevel,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            return CreateCommonReceiveModels(result.Rewards);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModels(
            IReadOnlyList<RewardModel> models)
        {
            var commonReceiveModels = models
                .Select(r => new CommonReceiveResourceModel(
                    r.UnreceivedRewardReasonType,
                    PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                    PlayerResourceModelFactory.Create(r.PreConversionResource)));

            return PlayerResourceSorter.Sort(commonReceiveModels).ToList();
        }


        public async UniTask<IReadOnlyList<CommonReceiveResourceModel>> ReceiveQuickRewardByAd(CancellationToken cancellationToken)
        {
            var result = await IdleIncentiveService.QuickReceiveByAd(cancellationToken);

            var prevUserParameterModel = GameRepository.GetGameFetch().UserParameterModel;

            UpdateGameModel(result.UsrParameter,
                result.UsrItems,
                result.UserConditionPacks,
                result.UserIdleIncentive);

            UserLevelUpCacheRepository.Save(
                result.UserLevel,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            return CreateCommonReceiveModels(result.Rewards);
        }

        void UpdateGameModel(
            UserParameterModel userParameter,
            IReadOnlyList<UserItemModel> items,
            IReadOnlyList<UserConditionPackModel> userConditionPacks,
            UserIdleIncentiveModel userIdleIncentive)
        {
            var gameFetch = GameRepository.GetGameFetch();
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetch = gameFetch with
            {
                UserParameterModel = userParameter,
            };

            var newGameFetchOther = gameFetchOther with
            {
                UserItemModels = gameFetchOther.UserItemModels.Update(items),
                UserConditionPackModels = gameFetchOther.UserConditionPackModels.Update(userConditionPacks),
                UserIdleIncentiveModel = userIdleIncentive
            };

            GameManagement.SaveGameUpdateAndFetch(newGameFetch, newGameFetchOther);
        }
    }
}
