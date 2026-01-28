using System;
using GLOW.Scenes.MessageBox.Presentation.View;
using GLOW.Scenes.MessageBox.Presentation.ViewModel;
using GLOW.Scenes.MessageBoxDetail.Presentation.ViewModel;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.Control
{
    public interface IMessageBoxDetailViewControl
    {
        public void ShowMessageBoxContentView(
            MessageBoxViewController viewController,
            MessageBoxDetailViewModel viewModel,
            Action<MessageBoxViewModel, bool, bool> onActionSelected,
            Action onMessageExpired);
    }
}
