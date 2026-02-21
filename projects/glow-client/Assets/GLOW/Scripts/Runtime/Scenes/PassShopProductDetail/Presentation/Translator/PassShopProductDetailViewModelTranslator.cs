using System.Linq;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.PassShopProductDetail.Domain.Model;
using GLOW.Scenes.PassShopProductDetail.Presentation.ViewModel;

namespace GLOW.Scenes.PassShopProductDetail.Presentation.Translator
{
    public class PassShopProductDetailViewModelTranslator
    {
        public static PassShopProductDetailViewModel ToPassShopProductDetailViewModel(PassShopProductDetailModel model)
        {
            return new PassShopProductDetailViewModel(
               model.PassIconAssetPath,
               model.PassProductName,
               model.PassDurationDay,
               model.PassEffectModels
                   .Select(PassEffectViewModelTranslator.ToEffectViewModel)
                   .ToList(),
               model.PassReceivableMaxRewardModels
                   .Select(PassReceivableRewardViewModelTranslator.ToPassReceivableRewardViewModel)
                   .ToList(),
               model.PassStartAt,
               model.PassEndAt,
               model.IsDisplayExpiration);
        }
    }
}