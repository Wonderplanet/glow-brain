using System.Linq;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.PassShopBuyConfirm.Domain.Model;
using GLOW.Scenes.PassShopBuyConfirm.Presentation.ViewModel;
using GLOW.Scenes.PassShopProductDetail.Presentation.Translator;

namespace GLOW.Scenes.PassShopBuyConfirm.Presentation.Translator
{
    public class PassShopBuyConfirmViewModelTranslator
    {
        public static PassShopBuyConfirmViewModel ToPassShopBuyConfirmViewModel(
            PassShopBuyConfirmModel model)
        {
            return new PassShopBuyConfirmViewModel(
                model.PassIconAssetPath,
                model.PassProductName,
                model.RawProductPriceText,
                model.PassEffectModels
                    .Select(PassEffectViewModelTranslator.ToEffectViewModel)
                    .ToList(),
                model.PassReceivableMaxRewardModels
                    .Select(PassReceivableRewardViewModelTranslator.ToPassReceivableRewardViewModel)
                    .ToList());
        }
    }
}