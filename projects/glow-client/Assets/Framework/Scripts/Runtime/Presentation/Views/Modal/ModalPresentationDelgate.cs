using UIKit;

namespace WPFramework.Presentation.Views
{
    public class ModalPresentationDelegate : IUIModalPresentationDelegate
    {
        readonly ModalPresentationContext _context = new ModalPresentationContext();

        public UIPresentationController GetModalPresentationController(UIViewController presented, UIViewController presenting)
        {
            return new ModalPresentationController(presented, presenting, _context);
        }
    }
}
