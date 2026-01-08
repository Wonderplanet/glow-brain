using GLOW.Core.Constants;
using GLOW.Scenes.AppTrackingTransparencyConfirm.Presentation.Views;
using WonderPlanet.OpenURLExtension;
using Zenject;

namespace GLOW.Scenes.AppTrackingTransparencyConfirm.Presentation.Presenters
{
    /// <summary>
    /// 11_タイトル
    /// 　11-2_ログイン
    /// 　　11-2-2_ATTダイアログ
    /// </summary>
    public class AppTrackingTransparencyConfirmPresenter : IAppTrackingTransparencyConfirmViewDelegate
    {
        [Inject] AppTrackingTransparencyConfirmViewController ViewController { get; }

        void IAppTrackingTransparencyConfirmViewDelegate.OnNextButtonTapped()
        {
            ViewController.Dismiss();
        }

        void IAppTrackingTransparencyConfirmViewDelegate.OnDetailButtonTapped()
        {
            // BNEのサイト表示
            CustomOpenURL.OpenURL(Credentials.TrackingDetailURL);
        }
    }
}
