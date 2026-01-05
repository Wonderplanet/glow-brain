using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.PassShop.Presentation.Translator
{
    public class PassShopProductViewModelTranslator
    {
        public static PassShopProductViewModel ToProductViewModel(PassShopProductModel model)
        {

            var dailyRewardViewModels = model.PassDailyRewardModels
                .Select(dailyReward => PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(dailyReward))
                .ToList();

            var immediatelyRewardViewModels = model.PassImmediatelyRewardModels
                .Select(immediatelyReward => PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(immediatelyReward))
                .ToList();

            return new PassShopProductViewModel(
                model.MstShopPassId,
                model.ShopProductId,
                model.PassProductName,
                model.PassDurationDay,
                model.ShopPassCellColor,
                model.IsDisplayExpiration,
                model.PassIconAssetPath,
                model.PassEffectModels
                    .Select(PassEffectViewModelTranslator.ToEffectViewModel)
                    .ToList(),
                dailyRewardViewModels,
                immediatelyRewardViewModels,
                model.RawProductPriceText,
                model.PassStartAt,
                model.PassEndAt,
                model.PassEffectValidRemainingTime
            );
        }
    }
}
