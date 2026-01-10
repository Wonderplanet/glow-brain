using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Modules.CommonWebView.Presentation.View
{
    public class CommonWebView : UIView
    {
        [SerializeField] UIText _titleText;

        [SerializeField] RectTransform _contentTransform;

        public UIText TitleText => _titleText;

        public RectTransform ContentTransform => _contentTransform;
    }
}
