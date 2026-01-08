using GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Views
{
    public class HomeHelpDialogViewController : UIViewController<HomeHelpDialogView>
    {
        [Inject] IHomeHelpDialogViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewWillAppear();
        }

        public void SetUp(HomeHelpViewModel viewModel)
        {
            ActualView.SetUp(viewModel);
        }

        [UIAction]
        void OnClose()
        {
            ViewDelegate.OnClose();
        }
    }
}
