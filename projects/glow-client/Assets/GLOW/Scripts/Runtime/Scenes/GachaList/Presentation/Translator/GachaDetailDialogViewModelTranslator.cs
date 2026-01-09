using GLOW.Scenes.GachaDetailDialog.Domain.Models;
using GLOW.Scenes.GachaDetailDialog.Presentation.ViewModels;

namespace GLOW.Scenes.GachaList.Presentation.Translator
{
    public static class GachaDetailDialogViewModelTranslator
    {
        public static GachaDetailDialogViewModel TranslateToViewModel(GachaDetailDialogUseCaseModel useCaseModel)
        {
            return new GachaDetailDialogViewModel(
                useCaseModel.AnnouncementContentsUrl,
                useCaseModel.GachaCautionContentsUrl);
        }
    }
}