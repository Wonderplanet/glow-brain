using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.GachaDetailDialog.Domain.Models;
using GLOW.Scenes.GachaDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaDetailDialog.Presentation.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Presentation.Presenters
{
    public class GachaDetailContentWireFrame : IGachaDetailContentWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }
        
        GachaDetailDialogViewController _gachaDetailDialogViewController;
        GachaDetailAnnouncementWebViewController _announcementWebViewController;
        GachaDetailCautionWebViewController _cautionWebViewController;
        GachaDetailTabType _currentTab = GachaDetailTabType.Announcement;
        
        void IGachaDetailContentWireFrame.ShowGachaDetailContent(
            GachaDetailDialogViewModel viewModel,
            GachaDetailDialogViewController gachaDetailDialogViewController) 
        {
            _gachaDetailDialogViewController = gachaDetailDialogViewController;
            
            if (!viewModel.AnnouncementContentsUrl.IsEmpty())
            {
                OpenAnnouncementWebView(viewModel.AnnouncementContentsUrl);
            }
            else
            {
                // お知らせがない場合はタブを非表示にして注意文言を表示する
                OpenCautionWebView(viewModel.GachaCautionContentsUrl);
            }
        }
        
        void IGachaDetailContentWireFrame.SwitchShowAnnouncementWebView(
            AnnouncementContentsUrl announcementContentsUrl)
        {
            if (_currentTab == GachaDetailTabType.Announcement || announcementContentsUrl.IsEmpty()) return;
            
            _cautionWebViewController?.Dismiss();
            _cautionWebViewController = null;
            
            OpenAnnouncementWebView(announcementContentsUrl);
        }
        
        void IGachaDetailContentWireFrame.SwitchShowCautionWebView(GachaCautionContentsUrl gachaCautionContentsUrl)
        {
            if(_currentTab == GachaDetailTabType.Caution || gachaCautionContentsUrl.IsEmpty()) return;
            
            _announcementWebViewController?.Dismiss();
            _announcementWebViewController = null;
            
            OpenCautionWebView(gachaCautionContentsUrl);
        }
        
        void OpenAnnouncementWebView(AnnouncementContentsUrl announcementContentsUrl)
        {
            if (_gachaDetailDialogViewController == null) return;
            
            _currentTab = GachaDetailTabType.Announcement;
            
            var controller = ViewFactory.Create<GachaDetailAnnouncementWebViewController>();
            
            _gachaDetailDialogViewController.AddChild(controller);
            // ContentTransformの子として追加
            controller.View.transform.SetParent(_gachaDetailDialogViewController.ActualView.ContentTransform, false);
            controller.BeginAppearanceTransition(true, true);
            controller.EndAppearanceTransition();
            controller.LoadURL(announcementContentsUrl);
            _announcementWebViewController = controller;
        }

        void OpenCautionWebView(GachaCautionContentsUrl gachaCautionContentsUrl)
        {
            if (_gachaDetailDialogViewController == null) return;
            
            _currentTab = GachaDetailTabType.Caution;

            var controller = ViewFactory.Create<GachaDetailCautionWebViewController>();
          
            _gachaDetailDialogViewController.AddChild(controller);
            // ContentTransformの子として追加
            controller.View.transform.SetParent(_gachaDetailDialogViewController.ActualView.ContentTransform, false);
            controller.BeginAppearanceTransition(true, true);
            controller.EndAppearanceTransition();
            controller.LoadURL(gachaCautionContentsUrl);
            _cautionWebViewController = controller;
        }
    }
}