using GLOW.Scenes.EmblemDetail.Domain.Models;
using GLOW.Scenes.EmblemDetail.Presentation.ViewModels;

namespace GLOW.Scenes.EmblemDetail.Presentation.Translators
{
    public static class EmblemDetailViewModelTranslator
    {
        public static EmblemDetailViewModel Translate(EmblemDetailModel model)
        {
            return new EmblemDetailViewModel(
                model.IconAssetPath,
                model.Name,
                model.Description
            );
        }
    }
}
