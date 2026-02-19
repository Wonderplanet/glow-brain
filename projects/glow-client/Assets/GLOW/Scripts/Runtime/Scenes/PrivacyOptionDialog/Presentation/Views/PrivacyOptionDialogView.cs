using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PrivacyOptionDialog.Presentation.Views
{
    public class PrivacyOptionDialogView : UIView
    {
        [SerializeField] RectTransform _contentTransform;

        public RectTransform ContentTransform => _contentTransform;
    }
}
