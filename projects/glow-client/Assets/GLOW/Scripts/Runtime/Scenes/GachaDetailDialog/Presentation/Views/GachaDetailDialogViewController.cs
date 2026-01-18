using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.GachaDetailDialog.Domain.Models;
using GLOW.Scenes.GachaDetailDialog.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-8-2_ガシャ詳細ダイアログ
    /// </summary>
    public class GachaDetailDialogViewController : UIViewController<GachaDetailDialogView>, IEscapeResponder
    {
        public record Argument(GachaDetailDialogViewModel ViewModel);

        [Inject] IGachaDetailViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
        GachaDetailTabType _currentTab = GachaDetailTabType.Announcement;
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            
            ViewDelegate.OnViewDidLoad();
        }
        
        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void SetViewModel(GachaDetailDialogViewModel viewModel)
        {
            // お知らせがない場合はタブを非表示
            if (viewModel.AnnouncementContentsUrl.IsEmpty())
            {
                ActualView.HideTabComponentGroup();
            }
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            Close();
            return true;
        }
        
        void Close()
        {
            ViewDelegate.OnClosed();
        }

        [UIAction]
        void OnClosed()
        {
            Close();
        }

        [UIAction]
        void OnClickAnnouncementButton()
        {
            if (_currentTab == GachaDetailTabType.Announcement) return;
            _currentTab = GachaDetailTabType.Announcement;
            ActualView.SwitchTab(GachaDetailTabType.Announcement);
            ViewDelegate.SwitchShowAnnouncementWebView();
        }

        [UIAction]
        void OnClickCautionButton()
        {
            if (_currentTab == GachaDetailTabType.Caution) return;
            _currentTab = GachaDetailTabType.Caution;
            ActualView.SwitchTab(GachaDetailTabType.Caution);
            ViewDelegate.SwitchShowCautionWebView();
        }
    }
}
