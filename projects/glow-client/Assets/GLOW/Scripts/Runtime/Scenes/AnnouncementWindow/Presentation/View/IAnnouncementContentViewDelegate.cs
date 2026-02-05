using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.View
{
    public interface IAnnouncementContentViewDelegate
    {
        void OnViewDidLoad();
        void OnBannerCellSelected(AnnouncementContentsUrl url);
    }
}