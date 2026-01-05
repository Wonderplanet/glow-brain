using System;
using GLOW.Scenes.MessageBox.Presentation.ViewModel;
using GLOW.Scenes.MessageBoxDetail.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.View
{
    public class MessageBoxDetailWithRewardViewController : UIViewController<MessageBoxDetailWithRewardView>
    {
        public record Argument(MessageBoxDetailViewModel ViewModel);
        [Inject] IMessageBoxDetailWithRewardViewDelegate ViewDelegate { get; }

        public Action<MessageBoxViewModel, bool, bool> OnReceiveCompleted { get; set; }

        public Action OnReceiveExpired { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
            ActualView.OnPlayerResourceIconCellTapped = ViewDelegate.OnRewardSelected;
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
        void OnReceiveButtonSelected()
        {
            ViewDelegate.OnReceiveRewardSelected();
        }

    }
}
