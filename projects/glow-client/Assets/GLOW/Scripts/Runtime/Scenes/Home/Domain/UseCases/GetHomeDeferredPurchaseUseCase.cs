using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Shop.Domain.Model;
using NUnit.Framework;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class GetHomeDeferredPurchaseUseCase
    {
        [Inject] IDeferredPurchaseCacheRepository DeferredPurchaseCacheRepository { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public HomeDeferredPurchaseResultModel GetDeferredPurchaseResult()
        {
            var errorCodes = DeferredPurchaseCacheRepository.GetAndResetDeferredPurchaseErrorCode();
            if (!errorCodes.IsEmpty())
            {
                return new HomeDeferredPurchaseResultModel(
                    new List<HomeDeferredPurchaseProductResultModel>(),
                    errorCodes
                );
            }

            var caches = new List<PurchaseResultCacheModel>();
            caches.AddRange(DeferredPurchaseCacheRepository.GetAndResetRestorePurchaseResults());
            caches.AddRange(DeferredPurchaseCacheRepository.GetAndResetDeferredPurchaseResults());
            if (!caches.Any()) return HomeDeferredPurchaseResultModel.Empty;

            var playerResourceModels = new List<HomeDeferredPurchaseProductResultModel>();
            foreach (var purchaseResult in caches)
            {
                playerResourceModels.Add(GetProductRewards(purchaseResult));
            }

            if (playerResourceModels.Count == 0) return HomeDeferredPurchaseResultModel.Empty;

            return new HomeDeferredPurchaseResultModel(
                playerResourceModels,
                new List<DeferredPurchaseErrorCode>()
            );
        }

        HomeDeferredPurchaseProductResultModel GetProductRewards(PurchaseResultCacheModel purchaseResult)
        {
            var commonReceiveResourceModels = CreateCommonReceiveModels(purchaseResult.Rewards);
            var productName = GetProductName(purchaseResult.UserStoreProductModel.ProductSubId);

            var product = MstShopProductDataRepository.GetStoreProducts()
                .FirstOrDefault(mst => mst.OprProductId == purchaseResult.UserStoreProductModel.ProductSubId, MstStoreProductModel.Empty);

            return new HomeDeferredPurchaseProductResultModel(product.ProductType, productName, commonReceiveResourceModels);
        }

        ProductName GetProductName(MasterDataId oprProductId)
        {
            // パック商品名
            var mstPack = MstShopProductDataRepository.GetPacks()
                .FirstOrDefault(mst => mst.ProductSubId == oprProductId, MstPackModel.Empty);
            if (!mstPack.IsEmpty()) return mstPack.ProductName;

            // パス商品名
            var mstPass = MstShopProductDataRepository.GetShopPasses()
                .FirstOrDefault(mst => mst.OprProductId == oprProductId, MstShopPassModel.Empty);
            if (!mstPass.IsEmpty()) return mstPass.PassProductName.ToProductName();

            // ダイヤ商品名
            var mstStoreProduct = MstShopProductDataRepository.GetStoreProducts()
                .FirstOrDefault(mst => mst.OprProductId == oprProductId, MstStoreProductModel.Empty);
            if (mstStoreProduct.IsEmpty()) return ProductName.Empty;

            var productName = ProductName.FromResourceTypeWithProductResourceAmount(ResourceType.PaidDiamond, mstStoreProduct.PaidAmount);
            return productName;
        }

        IReadOnlyList<CommonReceiveResourceModel> CreateCommonReceiveModels(IReadOnlyList<RewardModel> models)
        {
            return models
                .Select(rewardModel =>
                {
                    var preConversionResource = rewardModel.PreConversionResource.IsEmpty()
                        ? PlayerResourceModel.Empty
                        : PlayerResourceModelFactory.Create(
                            rewardModel.PreConversionResource.ResourceType,
                            rewardModel.PreConversionResource.ResourceId,
                            rewardModel.PreConversionResource.ResourceAmount.ToPlayerResourceAmount());
                    return new CommonReceiveResourceModel(
                        rewardModel.UnreceivedRewardReasonType,
                        PlayerResourceModelFactory.Create(rewardModel.ResourceType, rewardModel.ResourceId, rewardModel.Amount),
                        preConversionResource);
                })
                .ToList();
        }
    }
}
