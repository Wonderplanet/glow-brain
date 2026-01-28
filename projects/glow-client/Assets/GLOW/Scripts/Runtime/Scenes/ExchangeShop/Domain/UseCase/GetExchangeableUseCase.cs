using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;
using GLOW.Scenes.Shop.Domain.Calculator;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCase
{
    public class GetExchangeableUseCase
    {
        [Inject] IMstExchangeShopDataRepository MstShopDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ILimitAmountModelCalculator LimitAmountModelCalculator { get; }

        public ExchangeRewardType GetExchangeable(MasterDataId mstLineupId, ItemAmount itemAmount)
        {
            var product = MstShopDataRepository.GetTradeLineup(mstLineupId);

            // 購入後の所持数を計算、上限チェック
            var resourceAmount = new ItemAmount(itemAmount.Value * product.ResourceAmount.Value);
            if(IsLimit(product, resourceAmount)) return ExchangeRewardType.HasMaximumItem;

            var ownedItemAmount = ItemAmount.Empty;
            if (product.ExchangeCostType == ExchangeCostType.Coin)
            {
                ownedItemAmount = new ItemAmount(GameRepository.GetGameFetch().UserParameterModel.Coin.HasAmount);
            }
            else
            {
                ownedItemAmount = GameRepository.GetGameFetchOther().UserItemModels
                    .FirstOrDefault(item => item.MstItemId == product.CostItemId, UserItemModel.Empty)
                    .Amount;
            }

            if(ownedItemAmount < product.CostAmount * itemAmount) return ExchangeRewardType.ShortageItem;

            if (product.PurchasableCount.Value <= 0
                && !product.PurchasableCount.IsInfinity())
            {
                return ExchangeRewardType.ExchangeLimit;
            }

            return ExchangeRewardType.Exchangeable;
        }

        bool IsLimit(MstExchangeLineupModel exchangeLineupModel, ItemAmount itemAmount)
        {
            var limitCheckModel = new LimitCheckModel(
                exchangeLineupModel.ProductItemId,
                exchangeLineupModel.ProductResourceType,
                itemAmount.Value);

            var isLimitExceeded =
                LimitAmountModelCalculator.FilteringLimitAmount(new List<LimitCheckModel> { limitCheckModel });

            return isLimitExceeded.Count > 0;
        }
    }
}
