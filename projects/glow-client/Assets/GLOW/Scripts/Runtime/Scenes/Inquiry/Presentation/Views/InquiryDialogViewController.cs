using UIKit;
using Zenject;

namespace GLOW.Scenes.Inquiry.Presentation.View
{
    public class InquiryDialogViewController : UIViewController<InquiryDialogView>
    {
        public record Argument(InquiryDialogViewModel ViewModel);

        [Inject] IInquiryDialogViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnDidLoad();
        }

        public void Initialize(InquiryDialogViewModel viewModel)
        {
            ActualView.Initialize(viewModel);
        }

        [UIAction]
        void OnCopyUserID()
        {
            ViewDelegate.OnCopyUserID();
        }

        [UIAction]
        void OnInquiry()
        {
            ViewDelegate.OnInquiry();
        }

        [UIAction]
        void OnCancel()
        {
            ViewDelegate.OnCancel();
        }
    }
}
