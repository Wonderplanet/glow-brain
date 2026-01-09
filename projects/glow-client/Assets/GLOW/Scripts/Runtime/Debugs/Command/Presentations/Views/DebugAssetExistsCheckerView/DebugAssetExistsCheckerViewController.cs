using UIKit;
using Zenject;

namespace GLOW.Debugs.Command.Presentations.Views.DebugAssetExistsCheckerView
{
    public class DebugAssetExistsCheckerViewController : UIViewController<DebugAssetExistsCheckerView>
    {
        [Inject] IDebugAssetExistsCheckerViewDelegate ViewDelegate { get; }
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.Init();
        }

        public void SetViewModel(string text)
        {

            ActualView.LogAreaText.text = text;
        }

        [UIAction]
        void OnClose()
        {
            Dismiss();
        }
    }
}
