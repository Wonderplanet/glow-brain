using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.PassShop.Presentation.Translator
{
    public class HeldPassEffectDisplayViewModelTranslator
    {
        public static HeldPassEffectDisplayViewModel ToHeldPassEffectDisplayViewModel(
            HeldPassEffectDisplayModel model)
        {
            return new HeldPassEffectDisplayViewModel(
                model.DisplayHoldingPassBannerAssetPath,
                model.RemainingTimeSpan);
        }
    }
}