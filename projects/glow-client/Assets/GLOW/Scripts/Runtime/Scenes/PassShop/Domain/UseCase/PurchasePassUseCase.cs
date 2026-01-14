using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Updaters;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.PassShop.Domain.Updater;
using Zenject;

namespace GLOW.Scenes.PassShop.Domain.UseCase
{
    public class PurchasePassUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IShopPurchaseResultUpdater ShopPurchaseResultUpdater { get; }
        [Inject] IHeldPassEffectRepositoryUpdater HeldPassEffectRepositoryUpdater { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IStoreCoreModule StoreCoreModule { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public async UniTask<PurchasePassUseCaseModel> PurchasePass(CancellationToken cancellationToken, MasterDataId mstShopPassId)
        {
            var mstShopPass = MstShopProductDataRepository.GetShopPass(mstShopPassId);
            var oprProduct = ValidatedStoreProductRepository.GetValidatedStoreProducts()
                .FirstOrDefault(
                    product => product.MstStoreProduct.OprProductId == mstShopPass.OprProductId,
                    ValidatedStoreProductModel.Empty);

            if (mstShopPass.IsEmpty() || oprProduct.IsEmpty())
            {
                return PurchasePassUseCaseModel.Empty;
            }

            var result = await StoreCoreModule.BuyPassProduct(cancellationToken, mstShopPass.OprProductId);
            ShopPurchaseResultUpdater.UpdatePurchasePassResult(result);

            // 購入したパスの効果を登録
            HeldPassEffectRepositoryUpdater.RegisterPassEffect();

            var remainingTimeSpan = result.UserShopPassModel.EndAt - TimeProvider.Now;

            var model = new PurchasePassUseCaseModel(
                mstShopPass.PassProductName,
                remainingTimeSpan);

            return model;
        }
    }
}
