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
using GLOW.Scenes.PackShop.Domain.Calculator;
using GLOW.Scenes.PackShop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.PackShop.Domain.UseCase
{
    public class GetPackProductListUseCase
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IShopProductCacheRepository ProductCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] IPackShopProductEvaluator PackShopProductEvaluator { get; }

        public PackShopProductListModel GetPackProductList()
        {
            var validPacks = PackShopProductEvaluator.GetValidateProductList();

            var stageClearPackModels = validPacks.StageClearPacks
                .Select(mst => TranslatePackShopProduct(mst.MstPack, mst.ValidatedStoreProduct))
                .ToList();
            var normalPackModels = validPacks.NormalPacks
                .Select(mst => TranslatePackShopProduct(mst.MstPack, mst.ValidatedStoreProduct))
                .ToList();
            var dailyPackModels = validPacks.DailyPacks
                .Select(mst => TranslatePackShopProduct(mst.MstPack, mst.ValidatedStoreProduct))
                .ToList();

            var dailyPackRemainingTimeSpan = CalculateDailyPackRemainingTimeSpan();

            return new PackShopProductListModel(
                normalPackModels,
                dailyPackModels,
                stageClearPackModels,
                dailyPackRemainingTimeSpan);
        }

        bool IsValidProduct(MstPackModel pack, ValidatedStoreProductModel product)
        {
            return IsValidPurchasableCountByPackType(pack, product.MstStoreProduct)
                   && PackShopProductCalculator.IsValidSaleHour(pack, GameRepository, TimeProvider);
        }

        bool IsValidPurchasableCountByPackType(MstPackModel pack, MstStoreProductModel storeProduct)
        {
            if (pack.CostType == CostType.Cash)
            {
                // CashタイプはMstStoreProductModelとUserStoreProductModelの購入可能数をチェック
                return PackShopProductCalculator.IsValidPurchasableCount(storeProduct, GameRepository);
            }
            else
            {
                // Cash以外はMstPackModelとUserTradePackModelの取引可能数をチェック
                return PackShopProductCalculator.IsValidTradableCount(pack, GameRepository);
            }
        }

        RemainingTimeSpan CalculateDailyPackRemainingTimeSpan()
        {
            var dailyResetTime = DailyResetTimeCalculator.GetRemainingTimeToDailyReset();
            return new RemainingTimeSpan(dailyResetTime);
        }

        PackShopProductModel TranslatePackShopProduct(MstPackModel mstPack, ValidatedStoreProductModel validateProduct)
        {
            var mstProduct = validateProduct.MstStoreProduct;
            var contents = MstShopProductDataRepository.GetPackContents(mstPack.Id);
            var contentItems = contents
                .Select(content =>
                {
                    var amount = content.ResourceAmount;
                    if (content is { ResourceType: ResourceType.FreeDiamond })
                    {
                        amount = new ObscuredPlayerResourceAmount(content.ResourceAmount.Value + mstProduct.PaidAmount.Value);
                    }
                    return (content.ResourceType, content.ResourceId, amount);
                })
                .Select(c => PlayerResourceModelFactory.Create(
                    c.ResourceType,
                    c.ResourceId,
                    c.amount.ToPlayerResourceAmount()))
                .ToList();

            // IsFirstTimeFreeが有効で初回購入の場合は価格を0にする
            var costType = PackShopProductCalculator.ShouldApplyFirstTimeFree(mstPack, GameRepository)
                ? CostType.Free
                : mstPack.CostType;
            var productPrice = costType == CostType.Cash ? validateProduct.GetPrice() : mstPack.CostAmount;
            if (costType == CostType.Free)
            {
                productPrice = ProductPrice.Empty;
            }

            var isNew = ProductCacheRepository.DisplayedOprPackProductIds.All(id => id != mstProduct.Id);
            var endDateTime = EndDateTime.Empty;

            if (!mstPack.SaleHours.IsEmpty())
            {
                var userConditionPackModel = GameRepository.GetGameFetchOther().UserConditionPackModels
                    .First(condition => condition.MstPackId == mstPack.Id);
                endDateTime = new EndDateTime(userConditionPackModel.StartDate.AddHours(mstPack.SaleHours.Value));
            }
            else if (mstPack.IsDisplayExpiration)
            {
                endDateTime = new EndDateTime(mstProduct.EndDate);
            }
            else if (mstPack.PackType != PackType.Daily)
            {
                endDateTime = EndDateTime.Infinity;
            }

            var purchasableCount = mstPack.CostType is CostType.Cash
                ? PackShopProductCalculator.GetProductPurchasableCount(mstProduct, GameRepository)
                : PackShopProductCalculator.GetPackPurchasableCount(mstPack, GameRepository);

            var isFirstTimeFreeDisplay = new IsFirstTimeFreeDisplay(mstPack.IsFirstTimeFree);

            return new PackShopProductModel(
                mstPack.ProductSubId,
                new NewFlag(isNew),
                mstPack.ProductName,
                costType.ToDisplayShopProductType(),
                productPrice,
                validateProduct.RawProductPriceText,
                mstPack.DiscountRate,
                purchasableCount,
                endDateTime,
                contentItems,
                PackBannerAssetPath.FromAssetKey(mstPack.BannerAssetKey),
                mstPack.PackDecoration,
                isFirstTimeFreeDisplay
                );
        }
    }
}
