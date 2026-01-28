using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Services
{
    public interface IShopService
    {
        UniTask<TradeShopItemResultModel> TradeShopItem(
            CancellationToken cancellationToken,
            MasterDataId shopItemId);

        UniTask<PurchaseResultModel> Purchase(
            CancellationToken cancellationToken,
            MasterDataId productSubId,
            string receipt,
            string currencyCode,
            float price,
            string rawPrice);

        UniTask<PurchaseResultModel> PurchasePack(
            CancellationToken cancellationToken,
            MasterDataId productSubId,
            string receipt,
            int price,
            string rawPrice);

        UniTask<PurchasePassResultModel> PurchasePass(
            CancellationToken cancellationToken,
            MasterDataId productSubId,
            string receipt,
            string currencyCode,
            float price,
            string rawPrice);

        UniTask IssuePurchaseAllowance(
            CancellationToken cancellationToken,
            string productId,
            string productSubId,
            string currencyCode,
            float price);

        UniTask<UserStoreInfoModel> SetUserStoreInfo(
            CancellationToken cancellationToken,
            DateOfBirth dateOfBirth);

        UniTask<PurchaseHistoryResultModel> PurchaseHistory(
            CancellationToken cancellationToken);
    }
}
