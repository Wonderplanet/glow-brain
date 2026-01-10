using System;
using System.Collections.Generic;
using System.Linq;
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
using WonderPlanet.UnityStandard.Extension;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCase
{
    public class GetExchangeProductsUseCase
    {
        [Inject] IMstExchangeShopDataRepository MstExchangeShopDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public ExchangeShopUseCaseModel GetTradeShopProducts(MasterDataId mstExchangeId)
        {
            var mstTradeProduct = MstExchangeShopDataRepository.GetTradeProduct(mstExchangeId);

            // 開催期限内の商品だけ取り出してUseCaseModelに変換
            var cellUseCaseModels = mstTradeProduct.Lineups
                .Select(l => CreateExchangeShopCellUseCaseModel(l,mstTradeProduct.Id))
                .OrderBy(m => m.SortOrder)
                .ToList();

            var tradeShopName = MstExchangeShopDataRepository.GetTradeContents()
                .FirstOrDefault(x => x.Id == mstExchangeId, MstExchangeModel.Empty)
                .Name;

            var amountModels = CreateExchangeShopTopAmountModels(mstTradeProduct.Lineups);

            return new ExchangeShopUseCaseModel(
                tradeShopName,
                cellUseCaseModels,
                amountModels);
        }

        IReadOnlyList<ExchangeShopTopAmountModel> CreateExchangeShopTopAmountModels(IReadOnlyList<MstExchangeLineupModel> lineups)
        {
            return lineups
                .Select(l => (CreateCostItemAndAssetPath(l), mstExchangeLineupModel: l))
                .Distinct(l => l.Item1.Item2.Value)
                .Select(l =>
                {
                    // コインは特殊扱いで返す
                    if (l.mstExchangeLineupModel.ExchangeCostType == ExchangeCostType.Coin)
                    {
                        return new ExchangeShopTopAmountModel(
                            l.Item1.Item2,
                            GameRepository.GetGameFetch().UserParameterModel.Coin.ToItemAmount());
                    }

                    // ここ処理重いけどMax3種類くらいなので許容とする
                    var userItemAmount =
                        GameRepository.GetGameFetchOther().UserItemModels
                            .FirstOrDefault(i => i.MstItemId == l.Item1.Item1.Id, UserItemModel.Empty)
                            .Amount;

                    return new ExchangeShopTopAmountModel(l.Item1.Item2, userItemAmount);
                })
                .ToList();
        }

        ExchangeShopCellUseCaseModel CreateExchangeShopCellUseCaseModel(
            MstExchangeLineupModel mstExchangeLineupModel,
            MasterDataId mstExchangeId)
        {
            // 商品の情報とアイコンパスを作成
            var productItemData = CreateProductItemAndAssetPath(mstExchangeLineupModel);
            var productPlayerResource = productItemData.Item1;
            var productItemIconAssetPath = productItemData.Item2;

            // 消費するアイテムの情報とアイコンパスを作成
            var costItemData = CreateCostItemAndAssetPath(mstExchangeLineupModel);
            var costItem = costItemData.Item1;
            var costItemIconAssetPath = costItemData.Item2;

            // ユーザーの過去の取引履歴から購入可能回数を計算(交換可能回数が無制限の場合は無視)
            var purchasableCount = CreatePurchasableCount(mstExchangeId, mstExchangeLineupModel);

            // 残り時間を計算。無期限のものは毎月購入リセットがあるので月末を期限とする
            var remainingTime = GetEndOfMonthLimit(mstExchangeLineupModel.EndAt);

            return new ExchangeShopCellUseCaseModel(
                mstExchangeId,
                mstExchangeLineupModel.MstLineupId,
                remainingTime,
                purchasableCount,
                new ProductName(productPlayerResource.Name.Value),
                mstExchangeLineupModel.ProductResourceType,
                mstExchangeLineupModel.ResourceAmount,
                productItemIconAssetPath,
                new ItemName(costItem.Name.Value),
                mstExchangeLineupModel.ExchangeCostType,
                costItemIconAssetPath,
                mstExchangeLineupModel.CostAmount,
                mstExchangeLineupModel.SortOrder,
                productPlayerResource);
        }

        PurchasableCount CreatePurchasableCount(MasterDataId mstExchangeId, MstExchangeLineupModel mstExchangeLineupModel)
        {
            var purchasableCount = mstExchangeLineupModel.PurchasableCount.Value;
            if (!mstExchangeLineupModel.PurchasableCount.IsInfinity())
            {
                var userExchangeLineupModels = GameRepository.GetGameFetchOther().UsrExchangeLineupModels;
                var purchasedCount = userExchangeLineupModels
                    .Where(u => u.MstExchangeId == mstExchangeId)
                    .FirstOrDefault(u => u.MstExchangeLineupId == mstExchangeLineupModel.MstLineupId, UserExchangeLineupModel.Empty)
                    .PurchasedCount;
                purchasableCount = mstExchangeLineupModel.PurchasableCount.Value - purchasedCount.Value;
                if(purchasableCount <= 0) purchasableCount = 0;
            }

            return new PurchasableCount(purchasableCount);
        }

        RemainingTimeSpan GetEndOfMonthLimit(ExchangeShopEndTime endTime)
        {
            return endTime.IsUnlimited()
                ? new RemainingTimeSpan(DailyResetTimeCalculator.GetRemainingTimeToMonthlyReset())
                : CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, endTime.Value);
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
