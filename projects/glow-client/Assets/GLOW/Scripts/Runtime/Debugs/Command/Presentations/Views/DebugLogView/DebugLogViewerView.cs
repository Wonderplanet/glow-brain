using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Views
{
    public class DebugLogViewerView : UIView
    {
        [SerializeField] Text _logAreaText;
        [SerializeField] ScrollRect _rect;

        public void UpdateLog(string text, LogType type)
        {
            var input = type switch
            {
                LogType.Log => $"LOG: {text}\n",
                LogType.Assert => $"ASSERT: {text}\n",
                LogType.Warning => $"<color=yellow>WARNING:</color> {text}\n",
                LogType.Error => $"<color=red>ERROR:</color> {text}\n",
                LogType.Exception => $"<color=red>EXCEPTION:</color> {text}\n",
                _ => ""
            };
            _logAreaText.text += input;
            _rect.verticalNormalizedPosition = 0;
        }
        public void ClearText()
        {
            _logAreaText.text = "";
        }
    }
}
