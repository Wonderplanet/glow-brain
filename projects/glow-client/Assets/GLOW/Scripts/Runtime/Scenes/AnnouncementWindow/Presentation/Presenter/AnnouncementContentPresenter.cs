using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Scenes.AnnouncementWindow.Presentation.View;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.Presenter
{
    /// <summary>
    /// 121_メニュー
    /// 　121-3_お知らせ
    /// 　　121-3-1_お知らせ
    /// </summary>
    public class AnnouncementContentPresenter : IAnnouncementContentViewDelegate
    {
        [Inject] AnnouncementContentViewController ViewController { get; }
        [Inject] AnnouncementContentViewController.Argument Argument { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        
        public void OnViewDidLoad()
        {
            ViewController.SetViewModel(Argument.ViewModels);
            ViewController.PlayCellAppearanceAnimation();
        }

        public void OnBannerCellSelected(
            AnnouncementContentsUrl url)
        {
            CommonWebViewControl.ShowAnnouncementWebView(url, Argument.HookedPatternUrlInAnnouncements);
        }
    }
}