using UIKit;
using UnityEngine;

namespace GLOW.Scenes.GachaDetailDialog.Presentation.Views.Components
{
    public class GachaDetailAnnouncementWebView : UIView
    {
        [SerializeField] RectTransform _contentTransform;

        public RectTransform ContentTransform => _contentTransform;
    }
}
