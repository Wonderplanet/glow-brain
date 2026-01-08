using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.Tracker;
using GLOW.Core.Domain.Updaters;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Exceptions;
using GLOW.Core.Extensions;
using GLOW.Scenes.PassShop.Domain.Updater;
using Wonderplanet.IAP;
using Wonderplanet.IAP.PurchasingModule;
using Zenject;

namespace GLOW.Core.Domain.Modules
{
    public class StoreCoreModule :
        IStoreCoreModule,
        IIssuePurchaseAllowanceAPI,
        IPurchaseExecuteAPI,
        IInAppPurchaseRestoreDelegate,
        IInAppPurchaseDeferredDelegate,
        IDisposable
    {
        [Inject] IShopService ShopService { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IDeferredPurchaseCacheRepository DeferredPurchaseCacheRepository { get; }
        [Inject] IShopPurchaseResultUpdater ShopPurchaseResultUpdater { get; }
        [Inject] IAnalyticsTracker AnalyticsTracker { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IHeldPassEffectRepositoryUpdater HeldPassEffectRepositoryUpdater { get; }

        IAPManager _iapManager;

        IInAppPurchase InAppPurchase => _iapManager;

        void IDisposable.Dispose()
        {
            _iapManager?.Dispose();
        }

        async UniTask IStoreCoreModule.Initialize(CancellationToken cancellationToken, IReadOnlyList<ShopProductId> productIds)
        {
            if(null != _iapManager && InAppPurchase.IsInitialized) return;

#if UNITY_EDITOR || UNITY_IAP_FAKESTORE
            IProviderPurchasingModule purchasingModule = PreferenceRepository.InAppPurchaseFakeStoreMode switch
            {
                InAppPurchaseFakeStoreMode.StandardUser => new ProviderFakeStoreStandardUserPurchasingModule(),
                InAppPurchaseFakeStoreMode.DeveloperUser => new ProviderFakeStoreDeveloperUserPurchasingModule(),
                _ => new ProviderFakeStoreDefaultPurchasingModule()
            };
            _iapManager = new IAPManager(purchasingModule);
#else
            _iapManager = new IAPManager();
#endif

            _iapManager.Setup(this, this, this, this);

            var nativeProductIds = productIds
                .Select(shopProductId => shopProductId.ToString())
                .Where(productId => !string.IsNullOrEmpty(productId))
                .Distinct()
                .ToList();
            await InAppPurchase.Initialize(cancellationToken, nativeProductIds);
        }

        IReadOnlyList<ValidatedStoreProductModel> IStoreCoreModule.ValidateProducts(IReadOnlyList<MstStoreProductModel> mstStoreProducts)
        {
            var storeProducts = InAppPurchase.GetProducts();
            return mstStoreProducts
                .Join(storeProducts, mstProduct => mstProduct.ProductId.ToString(), storeProduct => storeProduct.ProductId, (mstProduct, storeProduct) => (mstProduct, storeProduct))
                .Select(product =>
                    new ValidatedStoreProductModel(
                        product.mstProduct,
                        new ProductPrice((float)product.storeProduct.Price),
                        new CurrencyCode(product.storeProduct.CurrencyCode),
                        new RawProductPriceText(product.storeProduct.RawPriceString)
                    )
                ).ToList();
        }

        bool IStoreCoreModule.IsPurchasedProductDeferred(ShopProductId productId)
        {
            return InAppPurchase.IsPurchasedProductDeferred(productId.Value);
        }

        async UniTask<PurchaseResultModel> IStoreCoreModule.BuyProduct(CancellationToken cancellationToken, MasterDataId oprProductId)
        {
            // TODO: mstStoreProductが見つからなかったら例外を投げる
            var validatedStoreProduct = ValidatedStoreProductRepository.GetValidatedStoreProducts()
                .FirstOrDefault(product => product.MstStoreProduct.OprProductId == oprProductId);
            if (validatedStoreProduct == null) throw new StoreProductNotFoundException();

            var iapProduct = InAppPurchase.GetProducts()
                .FirstOrDefault(product => product.ProductId == validatedStoreProduct.MstStoreProduct.ProductId.Value);

            var result = await InAppPurchase.IssuePurchaseAllowanceAndExecutePurchase(cancellationToken, iapProduct, oprProductId.Value);
            return result.ExecuteResult.Data as PurchaseResultModel;
        }

        async UniTask<PurchasePassResultModel> IStoreCoreModule.BuyPassProduct(CancellationToken cancellationToken, MasterDataId oprProductId)
        {
            // TODO: mstStoreProductが見つからなかったら例外を投げる
            var validatedStoreProduct = ValidatedStoreProductRepository.GetValidatedStoreProducts()
                .FirstOrDefault(product => product.MstStoreProduct.OprProductId == oprProductId);
            if (validatedStoreProduct == null) throw new StoreProductNotFoundException();

            var iapProduct = InAppPurchase.GetProducts()
                .FirstOrDefault(product => product.ProductId == validatedStoreProduct.MstStoreProduct.ProductId.Value);

            var result = await InAppPurchase.IssuePurchaseAllowanceAndExecutePurchase(cancellationToken, iapProduct, oprProductId.Value);
            return result.ExecuteResult.Data as PurchasePassResultModel;
        }

        // NOTE: 購入前にサーバ側で商品状態の確認(期間や回数など)と購入予約を行う
        async UniTask<StoreAllowance> IIssuePurchaseAllowanceAPI.Execute(CancellationToken cancellationToken, string productId, string productSubId)
        {
            var storeProduct = InAppPurchase.GetProducts()
                .FirstOrDefault(product => product.ProductId == productId);

            await ShopService.IssuePurchaseAllowance(cancellationToken, productId, productSubId, storeProduct.CurrencyCode, (float)storeProduct.Price);

            return new StoreAllowance() { ProductId = productId, ProductSubId = productSubId };
        }

        // NOTE: 課金後、サーバ側にレシートを渡してレシート検証＆商品付与する
        async UniTask<ExecuteResult> IPurchaseExecuteAPI.Execute(CancellationToken cancellationToken, IAPStoreReceipt storeReceipt)
        {
            var storeProductId = new ShopProductId(storeReceipt.ProductId);
            // TODO: このタイミングでRepositoryにアクセスしたくないので、ProductSubIdではなくProductIdを送るようにしたい
            var mstStoreProduct = MstShopProductDataRepository.GetStoreProducts()
                .FirstOrDefault(product => product.ProductId == storeProductId, MstStoreProductModel.Empty);

            var currencyCode = storeReceipt.CurrencyCode;
            var price = (float)storeReceipt.Price;
            var rawPriceString = storeReceipt.RawPriceString;
            var myId = GameRepository.GetGameFetchOther().UserProfileModel.MyId;


            // TODO: 本来ならパスもPurchaseを使って実行したいが、まだ統合ができていないため一旦これで対応
            // 統合予定
            if (mstStoreProduct.ProductType == ProductType.Pass)
            {
                var result = await ShopService.PurchasePass(
                    cancellationToken,
                    mstStoreProduct.OprProductId,
                    storeReceipt.Receipt,
                    currencyCode,
                    price,
                    rawPriceString);

                var dictionary = new Dictionary<string, object>()
                {
                    {TrackEventNameDefinitions.AppUserId, myId.Value},
                    {TrackEventNameDefinitions.Currency, currencyCode},
                    {TrackEventNameDefinitions.Revenue, price}
                };
                // AdjustとFirebaseでイベントを送る
                AnalyticsTracker.TrackRevenueAdjustEvent(
                    TrackEventNameDefinitions.AdjustPurchase,
                    price,
                    currencyCode);
                AnalyticsTracker.TrackFirebaseAnalyticsEvent(
                    TrackEventNameDefinitions.FirebasePurchase,
                    dictionary);

                return new ExecuteResult()
                {
                    ProductSubId = mstStoreProduct.OprProductId.Value,
                    Data = result
                };
            }
            else
            {
                var result = await ShopService.Purchase(
                    cancellationToken,
                    mstStoreProduct.OprProductId,
                    storeReceipt.Receipt,
                    currencyCode,
                    price,
                    rawPriceString);

                var dictionary = new Dictionary<string, object>()
                {
                    {TrackEventNameDefinitions.AppUserId, myId.Value},
                    {TrackEventNameDefinitions.Currency, currencyCode},
                    {TrackEventNameDefinitions.Revenue, price}
                };
                // AdjustとFirebaseでイベントを送る
                AnalyticsTracker.TrackRevenueAdjustEvent(
                    TrackEventNameDefinitions.AdjustPurchase,
                    price,
                    currencyCode);
                AnalyticsTracker.TrackFirebaseAnalyticsEvent(
                    TrackEventNameDefinitions.FirebasePurchase,
                    dictionary);

                return new ExecuteResult()
                {
                    ProductSubId = mstStoreProduct.OprProductId.Value,
                    Data = result
                };
            }
        }

        // 初期化後、リストア処理が終わったら呼び出される
        void IInAppPurchaseRestoreDelegate.OnRestored(IAPPurchaseResult purchaseResult)
        {
            if (purchaseResult.ExecuteResult.Data is PurchasePassResultModel purchasePassResultModel)
            {
                ShopPurchaseResultUpdater.UpdatePurchasePassResult(purchasePassResultModel);
                HeldPassEffectRepositoryUpdater.RegisterPassEffect();
                // パス購入の場合は遅延決済キャッシュには購入結果を変換して保存
                var convertedPurchaseResult = ConvertPassResultToPurchaseResult(purchasePassResultModel);
                DeferredPurchaseCacheRepository.AddRestorePurchaseResult(convertedPurchaseResult);
                return;
            }

            if (purchaseResult.ExecuteResult.Data is PurchaseResultModel purchaseResultModel)
            {
                ShopPurchaseResultUpdater.UpdatePurchaseResult(purchaseResultModel);
                var cacheModel = new PurchaseResultCacheModel(
                    purchaseResultModel.Rewards,
                    purchaseResultModel.UserStoreProductModel);
                DeferredPurchaseCacheRepository.AddRestorePurchaseResult(cacheModel);
            }
        }

        // 遅延決済が完了した際に呼び出される
        // 完全非同期なのでいつ呼び出されるか不定
        // アプリ終了状態で遅延決済した場合はリストア扱いになる
        void IInAppPurchaseDeferredDelegate.OnPurchased(IAPPurchaseResult purchaseResult)
        {
            if (purchaseResult.ExecuteResult.Data is PurchasePassResultModel purchasePassResultModel)
            {
                ShopPurchaseResultUpdater.UpdatePurchasePassResult(purchasePassResultModel);
                HeldPassEffectRepositoryUpdater.RegisterPassEffect();
                // パス購入の場合は遅延決済キャッシュには購入結果を変換して保存
                var convertedPurchaseResult = ConvertPassResultToPurchaseResult(purchasePassResultModel);
                DeferredPurchaseCacheRepository.AddDeferredPurchaseResult(convertedPurchaseResult);
                return;
            }

            if (purchaseResult.ExecuteResult.Data is PurchaseResultModel purchaseResultModel)
            {
                ShopPurchaseResultUpdater.UpdatePurchaseResult(purchaseResultModel);
                var cacheModel = new PurchaseResultCacheModel(
                    purchaseResultModel.Rewards,
                    purchaseResultModel.UserStoreProductModel);
                DeferredPurchaseCacheRepository.AddDeferredPurchaseResult(cacheModel);
            }
        }

        PurchaseResultCacheModel ConvertPassResultToPurchaseResult(PurchasePassResultModel purchasePassResult)
        {
            // パス購入結果を通常の購入結果に変換（ホーム画面での報酬表示用）
            return new PurchaseResultCacheModel(
                new List<RewardModel>(),
                purchasePassResult.UserStoreProductModel
            );
        }
    }
}
