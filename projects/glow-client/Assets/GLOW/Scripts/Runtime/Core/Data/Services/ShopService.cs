using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class ShopService : IShopService
    {
        [Inject] ShopApi ShopApi { get; }

        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }
        async UniTask<TradeShopItemResultModel> IShopService.TradeShopItem(
            CancellationToken cancellationToken,
            MasterDataId shopItemId)
        {
            try
            {
                var tradeResultData = await ShopApi.TradeShopItem(cancellationToken, shopItemId.Value);
                return TradeShopItemResultDataTranslator.ToTradeShopItemResultModel(tradeResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PurchaseResultModel> IShopService.Purchase(
            CancellationToken cancellationToken,
            MasterDataId productSubId,
            string receipt,
            string currencyCode,
            float price,
            string rawPrice)
        {
            try
            {
                var purchaseResultData = await ShopApi.Purchase(
                    cancellationToken,
                    productSubId.Value,
                    receipt,
                    currencyCode,
                    price,
                    rawPrice);

                return PurchaseResultDataTranslator.ToPurchaseResultModel(purchaseResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PurchaseResultModel> IShopService.PurchasePack(
            CancellationToken cancellationToken,
            MasterDataId productSubId,
            string receipt,
            int price,
            string rawPrice)
        {
            try
            {
                var purchaseResultData = await ShopApi.Purchase(
                    cancellationToken,
                    productSubId.Value,
                    receipt,
                    CurrencyCode.JPY.ToString(),
                    0,
                    "0");

                return PurchaseResultDataTranslator.ToPurchaseResultModel(purchaseResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PurchasePassResultModel> IShopService.PurchasePass(
            CancellationToken cancellationToken,
            MasterDataId productSubId,
            string receipt,
            string currencyCode,
            float price,
            string rawPrice)
        {
            try
            {
                var purchaseResultData = await ShopApi.PurchasePass(
                    cancellationToken,
                    productSubId.Value,
                    receipt,
                    currencyCode,
                    price,
                    rawPrice);

                return PurchasePassResultDataTranslator.ToPurchasePassResultModel(purchaseResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask IShopService.IssuePurchaseAllowance(
            CancellationToken cancellationToken,
            string productId,
            string productSubId,
            string currencyCode,
            float price)
        {
            try
            {
                await ShopApi.Allowance(cancellationToken, productId, productSubId, currencyCode, price);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<UserStoreInfoModel> IShopService.SetUserStoreInfo(
            CancellationToken cancellationToken,
            DateOfBirth dateOfBirth)
        {
            try
            {
                var result = await ShopApi.SetStoreInfo(cancellationToken, dateOfBirth.ToInt());
                return UserStoreInfoModelTranslator.ToUserStoreInfoModel(result.UsrStoreInfo);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PurchaseHistoryResultModel> IShopService.PurchaseHistory(CancellationToken cancellationToken)
        {
            try
            {
                var result = await ShopApi.PurchaseHistory(cancellationToken);
                return PurchaseHistoryResultDataTranslator.ToPurchaseHistoryResultModel(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}
