using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Modules;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.Tracker;
using GLOW.Core.Domain.Updaters;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Core.Extensions;
using GLOW.Scenes.PackShop.Domain.Calculator;
using GLOW.Scenes.PackShop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.PackShop.Domain.UseCase
{
    public class BuyPackShopProductUseCase
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IShopService ShopService { get; }
        [Inject] IShopPurchaseResultUpdater ShopPurchaseResultUpdater { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IStoreCoreModule StoreCoreModule { get; }
        [Inject] IGameRepository GameRepository { get; }

        public async UniTask<IReadOnlyList<CommonReceiveResourceModel>> BuyProduct(
            CancellationToken cancellationToken,
            MasterDataId oprProductId)
        {
            var mstPackModel = MstShopProductDataRepository.GetPacks()
                .First(mst => mst.ProductSubId == oprProductId);

            PurchaseResultModel result;

            // IsFirstTimeFreeが有効で初回購入の場合は無料として処理
            var costType = PackShopProductCalculator.ShouldApplyFirstTimeFree(mstPackModel, GameRepository)
                ? CostType.Free
                : mstPackModel.CostType;

            if (costType == CostType.Cash)
            {
                result = await StoreCoreModule.BuyProduct(cancellationToken, oprProductId);
            }
            else
            {
                // 非課金商品の場合は課金パラメータを空で送る
                var receipt = string.Empty;
                var priceValue = 0;
                var rawPrice = string.Empty;

                result = await ShopService.PurchasePack(
                    cancellationToken,
                    oprProductId,
                    receipt,
                    priceValue,
                    rawPrice);
            }

            // 副作用
            ShopPurchaseResultUpdater.UpdatePurchaseResult(result);

            return CreateCommonReceiveResourceModels(result.Rewards);
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveResourceModels(
            IReadOnlyList<RewardModel> rewards)
        {
            return rewards
                .Select(r =>
                    new CommonReceiveResourceModel(
                        r.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                        PlayerResourceModelFactory.Create(r.PreConversionResource)))
                .ToList();
        }
    }
}
