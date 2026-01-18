using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.GachaDetailDialog.Domain.Models;
using GLOW.Scenes.GachaDetailDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Presentation.Presenters
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-8-2_ガシャ詳細ダイアログ
    /// </summary>
    public class GachaDetailDialogPresenter : IGachaDetailViewDelegate
    {
        [Inject] GachaDetailDialogViewController ViewController { get; }
        [Inject] GachaDetailDialogViewController.Argument Argument { get; }
        [Inject] IGachaDetailContentWireFrame GachaDetailContentWireFrame { get; }
        
        AnnouncementContentsUrl _announcementContentsUrl = AnnouncementContentsUrl.Empty;
        GachaCautionContentsUrl _gachaCautionContentsUrl = GachaCautionContentsUrl.Empty;
        
        void IGachaDetailViewDelegate.OnViewDidLoad()
        {
            ViewController.SetViewModel(Argument.ViewModel);
            
            _announcementContentsUrl = Argument.ViewModel.AnnouncementContentsUrl;
            _gachaCautionContentsUrl = Argument.ViewModel.GachaCautionContentsUrl;
                
            GachaDetailContentWireFrame.ShowGachaDetailContent(Argument.ViewModel, ViewController);
        }

        void IGachaDetailViewDelegate.OnClosed()
        {
            ViewController.Dismiss();
        }

        void IGachaDetailViewDelegate.SwitchShowAnnouncementWebView()
        {
            GachaDetailContentWireFrame.SwitchShowAnnouncementWebView(_announcementContentsUrl);
        }

        void IGachaDetailViewDelegate.SwitchShowCautionWebView()
        {
            GachaDetailContentWireFrame.SwitchShowCautionWebView(_gachaCautionContentsUrl);
        }
    }
}
