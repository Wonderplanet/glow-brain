using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.PassShopBuyConfirm.Domain.Factory;
using GLOW.Scenes.PassShopBuyConfirm.Domain.Model;
using Zenject;

namespace GLOW.Scenes.PassShopBuyConfirm.Domain.UseCase
{
    public class ShowPassBuyConfirmUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IPassReceivableRewardModelFactory PassReceivableRewardModelFactory { get; }

        public PassShopBuyConfirmModel GetPassShopConfirmModel(MasterDataId mstShopPassId)
        {
            var mstShopPass = MstShopProductDataRepository.GetShopPass(mstShopPassId);
            var validatedStoreProduct = ValidatedStoreProductRepository.GetValidatedStoreProducts()
                .First(product => product.MstStoreProduct.OprProductId == mstShopPass.OprProductId);

            var mstEffects = MstShopProductDataRepository.GetShopPassEffects(mstShopPassId);
            var effects = mstEffects
                .Select(effect => new PassEffectModel(
                    effect.ShopPassEffectType,
                    effect.EffectValue))
                .ToList();
            
            var passReceivableRewardModel = PassReceivableRewardModelFactory.CreatePassReceivableRewardModels(mstShopPassId);

            return new PassShopBuyConfirmModel(
                PassIconAssetPath.FromAssetKey(mstShopPass.PassAssetKey),
                mstShopPass.PassProductName,
                validatedStoreProduct.RawProductPriceText,
                effects,
                passReceivableRewardModel);
        }
    }
}