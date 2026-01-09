using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.GachaDetailDialog.Domain.Models;
using GLOW.Scenes.GachaDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaDetailDialog.Presentation.Views;

namespace GLOW.Scenes.GachaDetailDialog.Presentation.Presenters
{
    public interface IGachaDetailContentWireFrame
    {
        void ShowGachaDetailContent(
            GachaDetailDialogViewModel viewModel,
            GachaDetailDialogViewController gachaDetailDialogViewController);
        void SwitchShowAnnouncementWebView(AnnouncementContentsUrl announcementContentsUrl);
        void SwitchShowCautionWebView(GachaCautionContentsUrl gachaCautionContentsUrl);
    }
}