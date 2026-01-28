using System;
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
using GLOW.Scenes.Shop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class GetStoreProductListUseCase
    {
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IShopProductCacheRepository ShopProductCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IShopProductModelCalculator ShopProductModelCalculator { get; }

        // 期間を表示するための閾値
        static readonly RemainingTimeSpan DisplayPurchasableTermThreshold = new (TimeSpan.FromDays(100));

        public IReadOnlyList<StoreProductModel> GetStoreProductList(MasterDataId masterDataId = default)
        {
            var nowTime = TimeProvider.Now;

            IReadOnlyList<ValidatedStoreProductModel> validatedStoreProductModels;
            if (masterDataId == default)
            {
                validatedStoreProductModels = ValidatedStoreProductRepository.GetValidatedStoreProducts()
                    .Where(storeProduct =>
                        storeProduct.MstStoreProduct.ProductType == ProductType.Diamond
                        && CalculateTimeCalculator.IsValidTime(
                            nowTime,
                            storeProduct.MstStoreProduct.StartDate,
                            storeProduct.MstStoreProduct.EndDate))
                    .Where(storeProduct => storeProduct.MstStoreProduct.ShouldDisplay())
                    .OrderByDescending(validatedProduct => validatedProduct.MstStoreProduct.DisplayPriority)
                    .ToList();

            }
            else
            {
                validatedStoreProductModels = ValidatedStoreProductRepository.GetValidatedStoreProducts()
                    .Where(storeProduct =>
                        storeProduct.MstStoreProduct.OprProductId == masterDataId
                        && storeProduct.MstStoreProduct.ProductType == ProductType.Diamond
                        && CalculateTimeCalculator.IsValidTime(
                            nowTime,
                            storeProduct.MstStoreProduct.StartDate,
                            storeProduct.MstStoreProduct.EndDate))
                    .Where(storeProduct => storeProduct.MstStoreProduct.ShouldDisplay())
                    .OrderByDescending(validatedProduct => validatedProduct.MstStoreProduct.DisplayPriority)
                    .ToList();
            }

            return CreateStoreProductListUseCaseModels(validatedStoreProductModels);
        }

        IReadOnlyList<StoreProductModel> CreateStoreProductListUseCaseModels(
            IReadOnlyList<ValidatedStoreProductModel> validateStoreProductModels)
        {
            return validateStoreProductModels.Select(validateProduct =>
            {
                var storeProduct = validateProduct.MstStoreProduct;

                var displayedShopProductIds = ShopProductCacheRepository.DisplayedShopProductIdHashSet;
                var isNew = !displayedShopProductIds.Contains(storeProduct.Id);

                var userStoreProductModel = GameRepository.GetGameFetchOther().UserStoreProductModels
                    .FirstOrDefault(
                        id => id.ProductSubId == storeProduct.OprProductId,
                        UserStoreProductModel.Empty);
                var currentPurchasableCount =
                    ShopProductModelCalculator.CalculatePurchasableCountCurrent(storeProduct, userStoreProductModel);
                var price = validateProduct.GetPrice();

                var shopProductAssetPath = storeProduct.ShopProductAssetKey.IsEmpty() ?
                    ShopProductAssetPath.Empty :
                    ShopProductAssetPath.FromAssetKey(storeProduct.ShopProductAssetKey);

                // 購入可能期間が100日以下の場合は、残り時間を表示する
                var purchasableTimeSpan = CalculateTimeCalculator.GetRemainingTime(
                    storeProduct.StartDate,
                    storeProduct.EndDate);
                var remainingTime = purchasableTimeSpan > DisplayPurchasableTermThreshold ?
                    RemainingTimeSpan.Empty :
                    CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, storeProduct.EndDate);

                return new StoreProductModel(
                    storeProduct.OprProductId,
                    DisplayCostType.Cash,
                    price,
                    validateProduct.RawProductPriceText,
                    ProductType.Diamond,
                    currentPurchasableCount,
                    remainingTime,
                    new NewFlag(isNew),
                    storeProduct.PaidAmount,
                    PlayerResourceModelFactory.Create(
                        ResourceType.PaidDiamond,
                        MasterDataId.Empty,
                        storeProduct.PaidAmount.ToPlayerResourceAmount()),
                    shopProductAssetPath);
            }).ToList();
        }
    }
}
