using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace WPFramework.Presentation.Views
{
    public class ModalContainerView : UIView
    {
        [SerializeField] Image backgroundImage;

        public Image BackgroundImage => backgroundImage;
    }
}
