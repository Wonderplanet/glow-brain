using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.PassShop.Presentation.Translator
{
    public class PassEffectViewModelTranslator
    {
        public static PassEffectViewModel ToEffectViewModel(PassEffectModel model)
        {
            return new PassEffectViewModel(model.PassEffectType, model.PassEffectValue);
        }
    }
}