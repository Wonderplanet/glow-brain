using System;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Exceptions;
using UnityEngine;
using Wonderplanet.IAP.Exception;
using Zenject;

namespace GLOW.Scenes.Login.Domain.UseCase
{
    public class InitializeIAPUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IStoreCoreModule StoreCoreModule { get; }
        [Inject] IDeferredPurchaseCacheRepository DeferredPurchaseCacheRepository { get; }

        public async UniTask Initialize(CancellationToken cancellationToken)
        {
            try
            {
                await InitializeIAP(cancellationToken);
            }
            catch (IAPRestoreException e)
            {
                foreach(var purchaseFailure in e.Failures)
                {
                    var exception = purchaseFailure.Exception;
                    var errorCode = exception switch
                    {
                        ServerBillingException serverBillingException =>
                            new DeferredPurchaseErrorCode(serverBillingException.ErrorCode),
                        IAPServerBillingTransactionEndPurchaseLimitException =>
                            new DeferredPurchaseErrorCode((int)ServerErrorCode.BillingTransactionEndPurchaseLimit),
                        IAPServerBillingTransactionEndException =>
                            new DeferredPurchaseErrorCode((int)ServerErrorCode.BillingTransactionEnd),
                        _ => DeferredPurchaseErrorCode.RestoreFailed
                    };
                    DeferredPurchaseCacheRepository.AddDeferredPurchaseErrorCode(errorCode);
                }
            }
            finally
            {
                ValidateProducts();
            }
        }

        async UniTask InitializeIAP(CancellationToken cancellationToken)
        {
            var mstAvailableProducts = MstShopProductDataRepository.GetStoreProducts();
            var productIds = mstAvailableProducts
                .Select(mst => mst.ProductId)
                .ToList();
            await StoreCoreModule.Initialize(cancellationToken, productIds);
        }

        void ValidateProducts()
        {
            var mstAvailableProducts = MstShopProductDataRepository.GetStoreProducts();
            var validatedProducts = StoreCoreModule.ValidateProducts(mstAvailableProducts);
            ValidatedStoreProductRepository.Register(validatedProducts);
        }
    }
}
