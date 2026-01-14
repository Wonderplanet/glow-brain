using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
using GLOW.Scenes.ExchangeShop.Domain.UseCaseModel;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;
using Zenject;
using System.Linq;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCase
{
    public class CreateExchangeConfirmUseCase
    {
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstExchangeShopDataRepository MstExchangeShopDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public ExchangeConfirmUseCaseModel CreateExchangeConfirm(MasterDataId mstExchangeId, MasterDataId mstExchangeLineupId)
        {
            // 商品データを取得
            var productModel = MstExchangeShopDataRepository.GetTradeProduct(mstExchangeId).Lineups
                .FirstOrDefault(lineup => lineup.MstLineupId == mstExchangeLineupId, MstExchangeLineupModel.Empty);

            // 商品の情報とアイコンパスを作成
            var productItemData = CreateProductItemAndAssetPath(productModel);
            var productPlayerResource = productItemData.Item1;
            var productItemIconAssetPath = productItemData.Item2;

            // 消費するアイテムの情報とアイコンパスを作成
            var costItemData = CreateCostItemAndAssetPath(productModel);
            var costItem = costItemData.Item1;
            var costItemIconAssetPath = costItemData.Item2;

            // ユーザーの過去の取引履歴から購入可能回数を計算
            var purchasedCount = CreatePurchaseCount(productModel, mstExchangeId);

            // 所持している消費アイテムの数を取得
            var costItemAmount = GetUserCostItemAmount(costItem.Id, productModel.ExchangeCostType);

            // 現在の最大購入可能数を計算(交換可能回数が無制限の場合は所持数のみで計算)
            var currentMaxPurchaseCount = CreateCurrentMaxPurchaseCount(mstExchangeId, productModel, costItemAmount);

            // 残り時間を計算。無期限のものは毎月購入リセットがあるので月末を期限とする
            var remainingTime = GetEndOfMonthLimit(productModel.EndAt);

            return new ExchangeConfirmUseCaseModel(
                productPlayerResource.Id,
                new ItemName(productPlayerResource.Name.Value),
                new ItemAmount(productModel.ResourceAmount.Value),
                productItemIconAssetPath,
                productPlayerResource.Rarity,
                costItem.Name,
                productModel.CostAmount,
                costItemIconAssetPath,
                costItemAmount,
                purchasedCount,
                currentMaxPurchaseCount,
                remainingTime);
        }

        PurchaseCount CreatePurchaseCount(MstExchangeLineupModel mstExchangeLineupModel, MasterDataId mstExchangeId)
        {
            PurchaseCount purchasedCount = PurchaseCount.Infinity;

            // 交換可能回数が無制限でない場合、過去の取引履歴から購入可能回数を計算
            if (!mstExchangeLineupModel.PurchasableCount.IsInfinity())
            {
                var gameFetchOther = GameRepository.GetGameFetchOther();
                var userExchangeLineupModel = gameFetchOther.UsrExchangeLineupModels
                    .Where(u => u.MstExchangeId == mstExchangeId)
                    .FirstOrDefault(u => u.MstExchangeLineupId == mstExchangeLineupModel.MstLineupId,
                        UserExchangeLineupModel.Empty);
                purchasedCount = new PurchaseCount(mstExchangeLineupModel.PurchasableCount.Value -
                                                   userExchangeLineupModel.PurchasedCount.Value);
            }

            return purchasedCount;
        }

        PurchaseCount CreateCurrentMaxPurchaseCount(
            MasterDataId mstExchangeId ,
            MstExchangeLineupModel mstExchangeLineupModel,
            ItemAmount userCostItemAmount)
        {
            var currentMaxPurchaseCount = userCostItemAmount.Value / mstExchangeLineupModel.CostAmount.Value;
            if (!mstExchangeLineupModel.PurchasableCount.IsInfinity())
            {
                var purchasedCount = CreatePurchaseCount(mstExchangeLineupModel, mstExchangeId);
                currentMaxPurchaseCount =
                    Math.Min(purchasedCount.Value, userCostItemAmount.Value / mstExchangeLineupModel.CostAmount.Value);
            }

            return new PurchaseCount(currentMaxPurchaseCount);
        }

        RemainingTimeSpan GetEndOfMonthLimit(ExchangeShopEndTime endTime)
        {
            return endTime.IsUnlimited()
                ? new RemainingTimeSpan(DailyResetTimeCalculator.GetRemainingTimeToMonthlyReset())
                : CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, endTime.Value);
        }

        ItemAmount GetUserCostItemAmount(MasterDataId costItemId, ExchangeCostType exchangeCostType)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var gameFetch = GameRepository.GetGameFetch();
            if (exchangeCostType == ExchangeCostType.Coin)
            {
                return new ItemAmount(gameFetch.UserParameterModel.Coin.HasAmount);
            }
            else
            {
                return gameFetchOther.UserItemModels
                    .FirstOrDefault(item => item.MstItemId == costItemId, UserItemModel.Empty)
                    .Amount;
            }
        }

        (PlayerResourceModel, ItemIconAssetPath) CreateProductItemAndAssetPath(MstExchangeLineupModel mstExchangeLineupModel)
        {
            var productResourceModel = PlayerResourceModelFactory.Create(
                mstExchangeLineupModel.ProductResourceType,
                mstExchangeLineupModel.ProductItemId,
                new PlayerResourceAmount(mstExchangeLineupModel.ResourceAmount.Value));

            var iconAssetPath = TranslateItemIconAssetPath(
                mstExchangeLineupModel.ProductResourceType,
                productResourceModel.AssetKey);

            return (productResourceModel, iconAssetPath);
        }

        (MstItemModel, ItemIconAssetPath) CreateCostItemAndAssetPath(MstExchangeLineupModel mstExchangeLineupModel)
        {
            var costItem = MstItemModel.Empty;
            var costItemIconAssetPath = ItemIconAssetPath.Empty;
            if (mstExchangeLineupModel.ExchangeCostType != ExchangeCostType.Coin)
            {
                costItem = MstItemDataRepository.GetItem(mstExchangeLineupModel.CostItemId);
                costItemIconAssetPath = ItemIconAssetPath.FromAssetKey(costItem.ItemAssetKey);
            }
            else
            {
                var coinAssetPath = CoinIconAssetPath.FromAssetKey(new CoinAssetKey());
                costItem = MstItemModel.Empty with
                {
                    Name = Coin.GetItemName()
                };
                costItemIconAssetPath = new ItemIconAssetPath(coinAssetPath.Value);
            }

            return (costItem, costItemIconAssetPath);
        }

        ItemIconAssetPath TranslateItemIconAssetPath(ResourceType resourceType, PlayerResourceAssetKey assetKey)
        {
            var iconAssetPath = ItemIconAssetPath.Empty;

            switch (resourceType)
            {
                case ResourceType.Item:
                    iconAssetPath = ItemIconAssetPath.FromAssetKey(assetKey);
                    break;
                case ResourceType.Coin:
                    var coinAssetPath = CoinIconAssetPath.FromAssetKey(new CoinAssetKey());
                    iconAssetPath = new ItemIconAssetPath(coinAssetPath.Value);
                    break;
                case ResourceType.FreeDiamond:
                    var diamondAssetPath = DiamondIconAssetPath.FromAssetKey(new DiamondAssetKey());
                    iconAssetPath = new ItemIconAssetPath(diamondAssetPath.Value);
                    break;
                case ResourceType.Artwork:
                    iconAssetPath = new ItemIconAssetPath(ArtworkAssetPath.Default.Value);
                    break;
                case ResourceType.Emblem:
                    var emblemAssetPath = EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey(assetKey.Value));
                    iconAssetPath = new ItemIconAssetPath(emblemAssetPath.Value);
                    break;
                case ResourceType.Unit:
                    var unitIconAssetPath = CharacterIconAssetPath.FromAssetKey(new UnitAssetKey(assetKey.Value));
                    iconAssetPath = new ItemIconAssetPath(unitIconAssetPath.Value);
                    break;
            }

            return iconAssetPath;
        }
    }
}
