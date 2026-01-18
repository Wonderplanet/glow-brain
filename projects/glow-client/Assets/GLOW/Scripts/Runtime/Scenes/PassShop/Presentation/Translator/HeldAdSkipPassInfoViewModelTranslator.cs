using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.PassShop.Presentation.Translator
{
    public class HeldAdSkipPassInfoViewModelTranslator
    {
        public static HeldAdSkipPassInfoViewModel ToHeldAdSkipPassInfoViewModel(HeldAdSkipPassInfoModel model)
        {
            if (model.IsEmpty()) return HeldAdSkipPassInfoViewModel.Empty;
            
            return new HeldAdSkipPassInfoViewModel(
                model.PassProductName,
                model.HeldRemainingTimeSpan);
        }
    }
}