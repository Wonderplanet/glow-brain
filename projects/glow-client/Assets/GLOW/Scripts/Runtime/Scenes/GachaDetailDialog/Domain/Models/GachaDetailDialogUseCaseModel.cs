using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaRatio.Domain.Model;

namespace GLOW.Scenes.GachaDetailDialog.Domain.Models
{
    public record GachaDetailDialogUseCaseModel(
        AnnouncementContentsUrl AnnouncementContentsUrl,
        GachaCautionContentsUrl GachaCautionContentsUrl)
    {
        public static GachaDetailDialogUseCaseModel Empty { get; } = new(
            AnnouncementContentsUrl.Empty,
            GachaCautionContentsUrl.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
