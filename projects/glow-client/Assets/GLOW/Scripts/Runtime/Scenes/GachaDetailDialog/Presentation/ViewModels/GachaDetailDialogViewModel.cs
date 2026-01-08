using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.GachaDetailDialog.Domain.Models;

namespace GLOW.Scenes.GachaDetailDialog.Presentation.ViewModels
{
    public record GachaDetailDialogViewModel(
        AnnouncementContentsUrl AnnouncementContentsUrl,
        GachaCautionContentsUrl GachaCautionContentsUrl
    );
}
