using GLOW.Scenes.TitleLinkBnIdDialog.Domain.Models;
using GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Models;

namespace GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Translators
{
    public class TitleLinkBnIdDialogViewModelTranslator
    {
        public static TitleLinkBnIdDialogViewModel Translate(TitleLinkBnIdDialogModel model)
        {
            return new TitleLinkBnIdDialogViewModel(
                model.UserName,
                model.UserLevel,
                model.BnIdLinkedFlag);
        }
    }
}
