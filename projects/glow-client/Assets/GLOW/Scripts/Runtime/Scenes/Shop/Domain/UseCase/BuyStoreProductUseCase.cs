using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Modules;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Tracker;
using GLOW.Core.Domain.Updaters;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.Shop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class BuyStoreProductUseCase
    {
        [Inject] IStoreCoreModule StoreCoreModule { get; }

        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IShopPurchaseResultUpdater ShopPurchaseResultUpdater { get; }

        public async UniTask<IReadOnlyList<CommonReceiveResourceModel>> BuyStoreProduct(CancellationToken ct, MasterDataId oprProductId)
        {
            // 通信
            var tradeResult = await StoreCoreModule.BuyProduct(ct, oprProductId);

            // 更新処理
            ShopPurchaseResultUpdater.UpdatePurchaseResult(tradeResult);

            var resultList = new List<CommonReceiveResourceModel>();

            // PurchaseResultModelのRewardsを追加
            var rewardModels = tradeResult.Rewards
                .Select(reward =>
                {
                    var playerResourceModel = PlayerResourceModelFactory.Create(
                        reward.ResourceType,
                        reward.ResourceId,
                        reward.Amount);
                    return new CommonReceiveResourceModel(
                        reward.UnreceivedRewardReasonType,
                        playerResourceModel,
                        PlayerResourceModelFactory.Create(reward.PreConversionResource));
                })
                .ToList();

            resultList.AddRange(rewardModels);

            return resultList;
        }
    }
}
