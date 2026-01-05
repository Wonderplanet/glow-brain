using UIKit;
using Zenject;

namespace GLOW.Scenes.AppTrackingTransparencyConfirm.Presentation.Views
{
    /// <summary>
    /// 11_タイトル
    /// 　11-2_ログイン
    /// 　　11-2-2_ATTダイアログ
    /// </summary>
    public class AppTrackingTransparencyConfirmViewController : UIViewController<AppTrackingTransparencyConfirmView>
    {
        [Inject] IAppTrackingTransparencyConfirmViewDelegate ViewDelegate { get; }

        [UIAction]
        void OnNextButtonTapped()
        {
            ViewDelegate.OnNextButtonTapped();
        }
        [UIAction]
        void OnDetailButtonTapped()
        {
            ViewDelegate.OnDetailButtonTapped();
        }
    }
}
