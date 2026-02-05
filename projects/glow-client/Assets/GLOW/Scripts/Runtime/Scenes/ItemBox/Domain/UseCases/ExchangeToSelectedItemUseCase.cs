using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using Zenject;

namespace GLOW.Scenes.ItemBox.Domain.UseCases
{
    public class ExchangeToSelectedItemUseCase
    {
        [Inject] IItemService ItemService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public async UniTask<IReadOnlyList<CommonReceiveResourceModel>> ExchangeItem(
            CancellationToken cancellationToken,
            MasterDataId mstItemId,
            MasterDataId selectedMstItemId,
            ItemAmount amount)
        {
            var result = await ItemService.ExchangeSelectItem(cancellationToken, mstItemId, selectedMstItemId, amount);

            var fetchOtherModel = GameRepository.GetGameFetchOther();
            var updatedFetchOtherModel = fetchOtherModel with
            {
                UserItemModels = fetchOtherModel.UserItemModels.Update(result.UserItemModels)
            };
            GameManagement.SaveGameFetchOther(updatedFetchOtherModel);

            return CreateCommonReceiveModels(result.ItemRewardModels);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModels(IReadOnlyList<RewardModel> models)
        {
            return models
                .Select(r => new CommonReceiveResourceModel(
                    r.UnreceivedRewardReasonType,
                    PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                    PlayerResourceModelFactory.Create(r.PreConversionResource)))
                .ToList();
        }
    }
}
