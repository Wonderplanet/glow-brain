using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.PassShopBuyConfirm.Domain.Factory;
using GLOW.Scenes.PassShopProductDetail.Domain.Model;
using Zenject;

namespace GLOW.Scenes.PassShopProductDetail.Domain.UseCase
{
    public class ShowPassShopProductDetailUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IPassReceivableRewardModelFactory PassReceivableRewardModelFactory { get; }

        public PassShopProductDetailModel GetPassShopProductDetail(MasterDataId mstShopPassId)
        {
            var mstShopPass = MstShopProductDataRepository.GetShopPass(mstShopPassId);

            var mstEffects = MstShopProductDataRepository.GetShopPassEffects(mstShopPassId);
            var effects = mstEffects
                .Select(effect => new PassEffectModel(
                    effect.ShopPassEffectType,
                    effect.EffectValue))
                .ToList();
            
            var passReceivableRewardModel = PassReceivableRewardModelFactory.CreatePassReceivableRewardModels(mstShopPassId);

            return new PassShopProductDetailModel(
                PassIconAssetPath.FromAssetKey(mstShopPass.PassAssetKey),
                mstShopPass.PassProductName,
                mstShopPass.PassDurationDays,
                effects,
                passReceivableRewardModel,
                mstShopPass.PassStartAt,
                mstShopPass.PassEndAt,
                mstShopPass.IsDisplayExpiration);
        }
    }
}