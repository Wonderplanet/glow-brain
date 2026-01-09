using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using Zenject;

namespace GLOW.Scenes.ItemBox.Domain.UseCases
{
    public class ConsumeItemUseCase
    {
        [Inject] IItemService ItemService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public async UniTask<IReadOnlyList<CommonReceiveResourceModel>> ConsumeItem(
            CancellationToken cancellationToken,
            MasterDataId mstItemId,
            ItemAmount amount)
        {
            var result = await ItemService.Consume(cancellationToken, mstItemId, amount);

            var fetchModel = GameRepository.GetGameFetch();
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = result.UserParameterModel,
            };
            var updatedFetchOtherModel = fetchOtherModel with
            {
                UserItemModels = fetchOtherModel.UserItemModels.Update(result.UserItemModels),
                UserItemTradeModels = fetchOtherModel.UserItemTradeModels.Update(result.UserItemTradeModel)
            };

            // 副作用
            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);

            return CreateCommonReceiveModels(result.ItemRewardModels);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModels(IReadOnlyList<RewardModel> rewardModels)
        {
            return rewardModels
                .Select(r =>
                    new CommonReceiveResourceModel(
                        r.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                        PlayerResourceModelFactory.Create(r.PreConversionResource)))
                .ToList();
        }
    }
}
