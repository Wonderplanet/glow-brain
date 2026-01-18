using GLOW.Scenes.Community.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.Community.Presentation.View
{
    /// <summary>
    /// 121_メニュー
    /// 　121-1メニュー（ホーム画面）
    /// 　121-5_メディア
    /// 　　121-5-1_メディア
    /// </summary>
    public class CommunityMenuViewController : UIViewController<CommunityMenuView>
    {
        [Inject] ICommunityMenuViewDelegate ViewDelegate { get; }
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetCommunityMenuListComponents(CommunityMenuViewModel viewModel)
        {
            ActualView.SetCommunityMenuListComponents(viewModel, OnCommunityBannerSelected);
        }
        
        void OnCommunityBannerSelected(CommunityMenuCellViewModel viewModel)
        {
            ViewDelegate.OnCommunityBannerSelected(viewModel);
        }
        
        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}