using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
using GLOW.Scenes.PassShop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.ShopTab.Domain.Factory
{
    public class ShowPassShopProductFactory : IShowPassShopProductFactory
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public IReadOnlyList<PassShopProductModel> GetPassShopProductList()
        {
            var passes = MstShopProductDataRepository.GetShopPasses();
            var validatedProducts = ValidatedStoreProductRepository.GetValidatedStoreProducts();

            var passShopProductModels = passes
                .Where(IsPassDisplayed)
                .Join(
                    validatedProducts,
                    mstPass => mstPass.OprProductId,
                    validatedProduct => validatedProduct.MstStoreProduct.OprProductId,
                    (mstPass, validatedProduct) => (mstPass, validatedProduct))
                .Select(mst => CreatePassShopProductModel(
                    mst.mstPass,
                    mst.validatedProduct))
                .ToList();

            return passShopProductModels;
        }

        bool IsPassDisplayed(MstShopPassModel model)
        {
            var fetchOther = GameRepository.GetGameFetchOther();
            var userPass = fetchOther.UserShopPassModels.FirstOrDefault(
                user => user.MstShopPassId == model.MstShopPassId,
                UserShopPassModel.Empty);
            var isPassValid = !userPass.IsEmpty() &&
                              CalculateTimeCalculator.IsValidTime(
                                  TimeProvider.Now,
                                  userPass.StartAt.Value,
                                  userPass.EndAt.Value);

            var isValid = CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                model.PassStartAt.Value,
                model.PassEndAt.Value);

            return isValid || isPassValid;
        }

        PassShopProductModel CreatePassShopProductModel(MstShopPassModel model, ValidatedStoreProductModel validatedProduct)
        {
            var mstEffects = MstShopProductDataRepository.GetShopPassEffects(model.MstShopPassId);
            var effects = mstEffects
                .Select(effect => new PassEffectModel(
                    effect.ShopPassEffectType,
                    effect.EffectValue))
                .ToList();

            var rewards = MstShopProductDataRepository.GetShopPassRewards(model.MstShopPassId);
            var dailyRewards = rewards
                .Where(reward => reward.ShopPassRewardType == ShopPassRewardType.Daily)
                .Select(reward => PlayerResourceModelFactory.Create(
                    reward.ResourceType,
                    reward.ResourceId,
                    reward.ResourceAmount.ToPlayerResourceAmount()))
                .ToList();
            var immediatelyRewards = rewards
                .Where(reward => reward.ShopPassRewardType == ShopPassRewardType.Immediately)
                .Select(reward => PlayerResourceModelFactory.Create(
                    reward.ResourceType,
                    reward.ResourceId,
                    reward.ResourceAmount.ToPlayerResourceAmount()))
                .ToList();

            var fetchOther = GameRepository.GetGameFetchOther();
            var userPass = fetchOther.UserShopPassModels.FirstOrDefault(
                user => user.MstShopPassId == model.MstShopPassId,
                UserShopPassModel.Empty);

            var remainingTimeSpan = userPass.IsEmpty() ?
                RemainingTimeSpan.Empty :
                userPass.EndAt - TimeProvider.Now;

            return new PassShopProductModel(
                model.MstShopPassId,
                model.ShopProductId,
                model.PassProductName,
                model.PassDurationDays,
                model.ShopPassCellColor,
                model.IsDisplayExpiration,
                PassIconAssetPath.FromAssetKey(model.PassAssetKey),
                effects,
                dailyRewards,
                immediatelyRewards,
                validatedProduct.RawProductPriceText,
                model.PassStartAt,
                model.PassEndAt,
                remainingTimeSpan);
        }
    }
}
