using UIKit;
using UnityEngine;

namespace GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Views
{
    public class LinkBnIdWebViewDialogView : UIView
    {
        [SerializeField] RectTransform _contentTransform;

        public RectTransform ContentTransform => _contentTransform;
    }
}
