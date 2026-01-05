using GLOW.Scenes.PvpInfo.Domain.Model;
using GLOW.Scenes.PvpInfo.Presentation.ViewModel;

namespace GLOW.Scenes.PvpInfo.Presentation.Translator
{
    public static class PvpInfoViewModelTranslator
    {
        public static PvpInfoViewModel Translate(PvpInfoUseCaseModel model)
        {
            return new PvpInfoViewModel(model.Description);
        }
    }
}
