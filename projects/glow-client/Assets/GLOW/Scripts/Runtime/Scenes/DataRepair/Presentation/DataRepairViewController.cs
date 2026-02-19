using UIKit;
using Zenject;

namespace GLOW.Scenes.DataRepair.Presentation
{
    /// <summary>
    /// 122_メニュー（タイトル画面）
    /// 　122-2_データ修復
    /// 　　122-2-1_データ修復
    /// </summary>
    public class DataRepairViewController : UIViewController<DataRepairView>
    {
        [Inject] IDataRepairViewDelegate ViewDelegate { get; }

        [UIAction]
        void OnClose()
        {
            Dismiss();
        }
        [UIAction]
        void OnRepair()
        {
            ViewDelegate.OnRepair();
        }
    }
}
