using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.MaintenanceDialog.Presentation.View
{
    public class MaintenanceDialogView : UIView
    {
        [SerializeField] UIText _messageText;

        public void SetMessage(string message)
        {
            _messageText.SetText(message);
        }
    }
}
