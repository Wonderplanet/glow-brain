using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.Shop.Domain.Extension;
using GLOW.Scenes.Shop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class GetShopProductListUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IShopProductCacheRepository ShopProductCacheRepository { get; }
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IShopProductModelCalculator ShopProductModelCalculator { get; }

        public IReadOnlyList<ShopProductModel> GetShopProductList(MasterDataId masterDataId = default)
        {
            var nowTime = TimeProvider.Now;
            if(masterDataId == default)
                return CreateShopProductListUseCaseModels(MstShopProductDataRepository.GetShopProducts().Where(shopProduct => CalculateTimeCalculator.IsValidTime(nowTime, shopProduct.StartDate, shopProduct.EndDate)).ToList());
            else
                return CreateShopProductListUseCaseModels(MstShopProductDataRepository.GetShopProducts().Where(shopProduct => shopProduct.Id == masterDataId && CalculateTimeCalculator.IsValidTime(nowTime, shopProduct.StartDate, shopProduct.EndDate)).ToList());
        }

        IReadOnlyList<ShopProductModel> CreateShopProductListUseCaseModels(
            IReadOnlyList<MstShopItemModel> mstShopProductModels)
        {
            return  mstShopProductModels.Select(shopProduct =>
            {
                var idleIncentiveCoinSetting = ShopProductModelCalculator.GetBaseCoinAmountAndIntervalTime(
                    MstIdleIncentiveRepository,
                    GameRepository);

                var rewardAmount = ShopProductModelCalculator.CalculateProductResourceAmount(
                    shopProduct,
                    idleIncentiveCoinSetting.baseCoinAmount,
                    idleIncentiveCoinSetting.intervalMinutes);

                if (shopProduct.ResourceType == ResourceType.IdleCoin && rewardAmount.IsEmpty())
                {
                    return ShopProductModel.Empty;
                }

                MstItemModel mstItemModel = shopProduct.ResourceType == ResourceType.Item
                    ? MstItemDataRepository.GetItem(shopProduct.ResourceId)
                    : MstItemModel.Empty;
                MstCharacterModel mstCharacterModel = shopProduct.ResourceType == ResourceType.Unit
                    ? MstCharacterDataRepository.GetCharacter(shopProduct.ResourceId)
                    : MstCharacterModel.Empty;

                var productName = ProductName.FromTypeAndName(
                    shopProduct.ResourceType,
                    mstItemModel.Name,
                    mstCharacterModel.Name,
                    shopProduct.ProductResourceAmount);

                var displayedShopProductIds = ShopProductCacheRepository.DisplayedShopProductIdHashSet;
                var isNew = !displayedShopProductIds.Contains(shopProduct.Id);

                var userShopItemModel = GameRepository.GetGameFetchOther().UserShopItemModels
                    .FirstOrDefault(
                        id => id.MstShopItemId == shopProduct.Id,
                        UserShopItemModel.Empty);
                var currentPurchasableCount =
                    ShopProductModelCalculator.CalculatePurchasableCountCurrent(shopProduct, userShopItemModel);

                var displayCostType = shopProduct.CostType.ToDisplayShopProductType();

                // 初回無料フラグが有効で、かつ初回購入がまだの場合
                var isFirstTimeFreeDisplay = new IsFirstTimeFreeDisplay(shopProduct.IsFirstTimeFree.IsEnable() &&
                                                                        currentPurchasableCount ==
                                                                        shopProduct.PurchasableCount);

                var costAmount = isFirstTimeFreeDisplay.IsEnable() ? new CostAmount(0) : shopProduct.CostAmount;

                return new ShopProductModel(
                    shopProduct.Id,
                    shopProduct.ResourceId,
                    productName,
                    shopProduct.ProductResourceAmount,
                    shopProduct.ShopType.ToDisplayShopProductType(),
                    displayCostType,
                    costAmount,
                    isFirstTimeFreeDisplay,
                    new NewFlag(isNew),
                    currentPurchasableCount,
                    shopProduct.ResourceType,
                    PlayerResourceModelFactory.Create(
                        shopProduct.ResourceType,
                        shopProduct.ResourceId,
                        rewardAmount),
                    GetItemModel(mstItemModel, shopProduct.ProductResourceAmount),
                    ShopProductAssetPath.Empty);
            }).Where(model => !model.IsEmpty()).ToList();
        }

        ItemModel GetItemModel(MstItemModel mstItemModel, ProductResourceAmount amount)
        {
            if (mstItemModel.IsEmpty())
                return ItemModel.Empty;

            return new ItemModel(
                mstItemModel.Id,
                mstItemModel.Name,
                mstItemModel.Description,
                mstItemModel.Type,
                mstItemModel.GroupType,
                mstItemModel.Rarity,
                mstItemModel.SortOrder,
                mstItemModel.ItemAssetKey,
                new ItemAmount(amount.Value),
                mstItemModel.MstSeriesId,
                mstItemModel.StartAt,
                mstItemModel.EndAt);
        }
    }
}
