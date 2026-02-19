using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AgreementDialog.Presentation.Views
{
    public class AgreementDialogView : UIView
    {
        [SerializeField] RectTransform _contentTransform;

        public RectTransform ContentTransform => _contentTransform;
    }
}
