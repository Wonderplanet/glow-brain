using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Views.DebugAssetExistsCheckerView
{
    public class DebugAssetExistsCheckerView : UIView
    {
        [SerializeField] Text _logAreaText;

        public Text LogAreaText => _logAreaText;
    }
}
