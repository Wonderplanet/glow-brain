using System;
using GLOW.Scenes.MessageBox.Presentation.ViewModel;
using GLOW.Scenes.MessageBoxDetail.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.View
{
    public class MessageBoxDetailViewController : UIViewController<MessageBoxDetailView>
    {
        public record Argument(MessageBoxDetailViewModel ViewModel);
        [Inject] IMessageBoxDetailViewDelegate ViewDelegate { get; }

        public Action<MessageBoxViewModel, bool, bool> OnOpenCompleted { get; set; }
        
        public Action OnOpenExpired { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void SetViewModel(MessageBoxDetailViewModel viewModel)
        {
            ActualView.SetViewModel(viewModel);
        }

        [UIAction]
        void OnClose()
        {
            ViewDelegate.OnClose();
        }

        [UIAction]
        void OnOpen()
        {
            ViewDelegate.OnOpenSelected();
        }
    }
}
