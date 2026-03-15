using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
using GLOW.Scenes.Shop.Domain.Constants;
using GLOW.Scenes.Shop.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class CheckShopPurchaseLimitUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IStoreCoreModule StoreCoreModule { get; }

        public OverShopPurchaseLimitFlag CheckShopPurchaseLimit(
            MasterDataId targetId,
            ShopPassFlag isShopPass)
        {
            var userStoreInfoModel = GameRepository.GetGameFetchOther().UserStoreInfoModel;
            MasterDataId productId = targetId;
            if (isShopPass)
            {
                var mstShopPass = MstShopProductDataRepository.GetShopPass(targetId);
                productId = mstShopPass.OprProductId;
            }

            var boughtProduct = ValidatedStoreProductRepository.GetValidatedStoreProducts()
                .Where(product => product.MstStoreProduct.OprProductId == productId)
                .FirstOrDefault(ValidatedStoreProductModel.Empty);
            if (boughtProduct.IsEmpty())
            {
                // 購入履歴がないということなのでfalseを返す
                return OverShopPurchaseLimitFlag.False;
            }

            var deferredPurchaseTotalPrice = ValidatedStoreProductRepository
                .GetValidatedStoreProducts()
                .Where(product => StoreCoreModule.IsPurchasedProductDeferred(product.MstStoreProduct.ProductId))
                .Sum(product => product.StorePrice.Value);

            var checkPrice = userStoreInfoModel.CurrentMonthTotalBilling.Value  // 今月の課金合計
                             + boughtProduct.StorePrice.Value       // 今回購入しようとしている商品の価格
                             + deferredPurchaseTotalPrice;          // 保留中の購入商品の価格

            // 年齢チェック
            if (userStoreInfoModel.UserAge < ShopConst.YoungAge)
            {
                if (checkPrice > ShopConst.YoungPurchaseLimit)
                {
                    // 上限を超えたのでtrueを返す
                    return OverShopPurchaseLimitFlag.True;
                }
            }
            else if (userStoreInfoModel.UserAge < ShopConst.AdultAge)
            {
                if (checkPrice > ShopConst.AdultPurchaseLimit)
                {
                    // 上限を超えたのでtrueを返す
                    return OverShopPurchaseLimitFlag.True;
                }
            }

            // 上限ないのでfalseで返す
            return OverShopPurchaseLimitFlag.False;
        }
    }
}
