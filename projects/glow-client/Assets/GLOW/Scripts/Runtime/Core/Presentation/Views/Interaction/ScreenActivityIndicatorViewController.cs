using GLOW.Modules.CommonToast.Presentation;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Core.Presentation.Views.Interaction
{
    public sealed class ScreenActivityIndicatorViewController : UIViewController<ScreenActivityIndicatorView>, IEscapeResponder
    {
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidDisappear()
        {
            base.ViewDidDisappear();

            EscapeResponderRegistry.Unregister(this);
        }
        
        public bool OnEscape()
        {
            CommonToastWireFrame.ShowInvalidOperationMessage();
            return true;
        }
    }
}
