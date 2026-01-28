using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
using GLOW.Scenes.PackShop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.PackShop.Domain.Calculator
{
    public class PackShopProductEvaluator : IPackShopProductEvaluator
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        PackProductEvaluateModel IPackShopProductEvaluator.GetValidateProductList()
        {
            var storeProductAll = ValidatedStoreProductRepository.GetValidatedStoreProducts();
            var packAll = MstShopProductDataRepository.GetPacks();

            var packProductList = storeProductAll
                .Where(product => product.MstStoreProduct.ProductType == ProductType.Pack)
                .ToList();

            var validPacks =
                CreateValidPackShopValidateProductModels(packAll, packProductList);

            var stageClearPacks = validPacks
                .Where(mst => mst.MstPack.SaleConditionValue.Condition == SaleCondition.StageClear)
                .Where(mst => PackShopProductCalculator.IsValidStageClear(mst.MstPack, GameRepository))
                .ToList();

            var otherPacks = validPacks
                .Where(mst => mst.MstPack.SaleConditionValue.Condition != SaleCondition.StageClear)
                .Where(mst =>
                    mst.MstPack.SaleConditionValue == SaleConditionValue.Empty
                    || PackShopProductCalculator.IsValidUserLevel(mst.MstPack, GameRepository)
                    || PackShopProductCalculator.IsValidElapseDays(mst.MstPack))
                .ToList();

            var normalPackModels = otherPacks
                .Where(mst => mst.MstPack.PackType == PackType.Normal)
                .ToList();
            var dailyPackModels = otherPacks
                .Where(mst => mst.MstPack.PackType == PackType.Daily)
                .ToList();

            return new PackProductEvaluateModel(
                stageClearPacks,
                normalPackModels,
                dailyPackModels);
        }

        List<PackShopValidateProductModel> CreateValidPackShopValidateProductModels(
            IReadOnlyList<MstPackModel> packAll,
            List<ValidatedStoreProductModel> packProductList)
        {
            return packAll
                .Select(pack =>
                {
                    var validatedProduct = GetValidatedStoreProduct(pack, packProductList);
                    if (validatedProduct.IsEmpty() || !IsValidProduct(pack, validatedProduct))
                    {
                        return null;
                    }

                    return new PackShopValidateProductModel(pack, validatedProduct);
                })
                .Where(pack => pack != null)
                .Where(pack => !pack.ValidatedStoreProduct.MstStoreProduct.IsEmpty()
                               && pack.ValidatedStoreProduct.MstStoreProduct.ShouldDisplay())
                .Where(pack => CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    pack.ValidatedStoreProduct.MstStoreProduct.StartDate,
                    pack.ValidatedStoreProduct.MstStoreProduct.EndDate))
                .OrderByDescending(model => model.MstPack.IsRecommended.Flg)
                .ThenByDescending(model => model.ValidatedStoreProduct.MstStoreProduct.DisplayPriority.Value)
                .ToList();
        }

        ValidatedStoreProductModel GetValidatedStoreProduct(MstPackModel pack, List<ValidatedStoreProductModel> productList)
        {
            if (pack.CostType == CostType.Cash)
            {
                return productList
                    .FirstOrDefault(
                        product => product.MstStoreProduct.OprProductId == pack.ProductSubId,
                        ValidatedStoreProductModel.Empty);
            }
            else
            {
                var mstStoreProduct = MstShopProductDataRepository.GetStoreProducts()
                    .FirstOrDefault(mstProduct => mstProduct.OprProductId == pack.ProductSubId);

                if (mstStoreProduct == null)
                {
                    return ValidatedStoreProductModel.Empty;
                }

                return new ValidatedStoreProductModel(
                    mstStoreProduct,
                    ProductPrice.Empty,
                    CurrencyCode.Empty,
                    RawProductPriceText.Empty);
            }
        }

        bool IsValidProduct(MstPackModel pack, ValidatedStoreProductModel product)
        {
            // デイリーパックは購入回数上限時も表示するのでバリデーションしない
            if (pack.PackType == PackType.Daily)
            {
                return PackShopProductCalculator.IsValidSaleHour(pack, GameRepository, TimeProvider)
                       && IsValidPackCondition(pack);
            }
            else
            {
                return IsValidPurchasableCountByPackType(pack, product.MstStoreProduct)
                       && PackShopProductCalculator.IsValidSaleHour(pack, GameRepository, TimeProvider)
                       && IsValidPackCondition(pack);
            }
        }

        bool IsValidPurchasableCountByPackType(MstPackModel pack, MstStoreProductModel storeProduct)
        {
            if (pack.CostType == CostType.Cash)
            {
                return PackShopProductCalculator.IsValidPurchasableCount(storeProduct, GameRepository);
            }
            else
            {
                return PackShopProductCalculator.IsValidTradableCount(pack, GameRepository);
            }
        }

        bool IsValidPackCondition(MstPackModel pack)
        {
            if (pack.SaleConditionValue.Condition == SaleCondition.StageClear)
            {
                return PackShopProductCalculator.IsValidStageClear(pack, GameRepository);
            }

            return pack.SaleConditionValue == SaleConditionValue.Empty
                   || PackShopProductCalculator.IsValidUserLevel(pack, GameRepository)
                   || PackShopProductCalculator.IsValidElapseDays(pack);
        }
    }
}
