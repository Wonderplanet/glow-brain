using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace WPFramework.Presentation.Views
{
    [RequireComponent(typeof(UICanvas))]
    public class ModalContext : MonoBehaviour, IEscapeResponder
    {
        public bool EscapeDismiss { get; set; }
        [SerializeField] RuntimeAnimatorController defaultAnimator;

        public RuntimeAnimatorController DefaultAnimator => defaultAnimator;

        UIViewController GetRootViewController()
        {
            var canvas = GetComponent<UICanvas>();
            return canvas.RootViewController;
        }

        void Awake()
        {
            EscapeDismiss = true;
            UIEscapeResponder.Main.Bind(this, this);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (!EscapeDismiss)
            {
                return false;
            }

            var controller = GetRootViewController();
            var hostingItem = controller.ChildViewControllers[0];
            if (hostingItem.IsBeingDismissed)
            {
                return true;
            }

            UISoundEffector.Main.PlaySeEscape();
            hostingItem.Dismiss();
            return true;
        }
    }
}
