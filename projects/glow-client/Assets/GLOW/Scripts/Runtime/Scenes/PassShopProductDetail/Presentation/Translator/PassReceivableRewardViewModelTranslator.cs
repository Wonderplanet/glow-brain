using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.PassShopProductDetail.Domain.Model;
using GLOW.Scenes.PassShopProductDetail.Presentation.ViewModel;

namespace GLOW.Scenes.PassShopProductDetail.Presentation.Translator
{
    public class PassReceivableRewardViewModelTranslator
    {
        public static PassReceivableRewardViewModel ToPassReceivableRewardViewModel(PassReceivableRewardModel model)
        {
            return new PassReceivableRewardViewModel(
                model.ProductName,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                    model.PlayerResourceModel),
                model.DailyReceivableAmount);
        }
    }
}