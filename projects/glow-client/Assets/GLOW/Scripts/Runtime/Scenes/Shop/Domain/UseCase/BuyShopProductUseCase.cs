using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.Shop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class BuyShopProductUseCase
    {
        [Inject] IShopService ShopService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IShopProductModelCalculator ShopProductModelCalculator { get; }

        public async UniTask<CommonReceiveResourceModel> BuyProduct(CancellationToken ct, MasterDataId id)
        {
            // 通信
            var tradeResult = await ShopService.TradeShopItem(ct, id);

            // 更新処理
            var (updatedFetchModel, updatedFetchOtherModel) = UpdateTradeResult(tradeResult);
            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);

            var boughtProductList = MstShopProductDataRepository.GetShopProducts()
                .Where(product => product.Id == id)
                .Select(product =>
                {
                    var idleIncentiveCoinSetting = ShopProductModelCalculator.GetBaseCoinAmountAndIntervalTime(
                        MstIdleIncentiveRepository,
                        GameRepository);
                    var playerResourceModel = PlayerResourceModelFactory.Create(
                        product.ResourceType,
                        product.ResourceId,
                        ShopProductModelCalculator.CalculateProductResourceAmount(
                            product,
                            idleIncentiveCoinSetting.baseCoinAmount,
                            idleIncentiveCoinSetting.intervalMinutes));
                    return new CommonReceiveResourceModel(
                            UnreceivedRewardReasonType.None, //TODO: サーバーからRewardDataで送って貰う必要があるかも
                            playerResourceModel,
                            PlayerResourceModel.Empty);
                })
                .ToList();

            if (boughtProductList.IsEmpty())
                return null;

            return boughtProductList.First();
        }

        (GameFetchModel, GameFetchOtherModel) UpdateTradeResult(TradeShopItemResultModel tradeResult)
        {
            var fetchModel = GameRepository.GetGameFetch();
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var currentParameterModel = tradeResult.UserParameterModel;
            var userItemModels = tradeResult.UserItemModels;
            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = currentParameterModel
            };

            var updatedFetchOtherModel = fetchOtherModel with
            {
                UserItemModels = fetchOtherModel.UserItemModels.Update(userItemModels),
                UserShopItemModels = fetchOtherModel.UserShopItemModels.Update(tradeResult.UserShopItemModels)
            };

            return (updatedFetchModel, updatedFetchOtherModel);
        }
    }
}
