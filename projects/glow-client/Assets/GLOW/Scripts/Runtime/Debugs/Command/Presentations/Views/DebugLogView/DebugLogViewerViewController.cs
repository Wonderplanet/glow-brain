using UIKit;
using UnityEngine;

namespace GLOW.Debugs.Command.Presentations.Views
{
    public class DebugLogViewerViewController : UIViewController<DebugLogViewerView>
    {
        public override void ViewDidLoad()
        {
            ActualView.ClearText();
            Application.logMessageReceived += LoggedCb;
        }
        public void LoggedCb(string logstr, string stacktrace, LogType type)
        {
            ActualView.UpdateLog(logstr, type);
        }
        [UIAction]
        void OnClose()
        {
            Dismiss();
        }
        [UIAction]
        void OnClear()
        {
            ActualView.ClearText();

        }
    }
}
