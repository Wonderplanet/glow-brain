using System;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Scenes.MessageBox.Presentation.View;
using GLOW.Scenes.MessageBox.Presentation.ViewModel;
using GLOW.Scenes.MessageBoxDetail.Presentation.View;
using GLOW.Scenes.MessageBoxDetail.Presentation.ViewModel;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.Control
{
    public class MessageBoxDetailViewControl : IMessageBoxDetailViewControl
    {
        [Inject] IViewFactory ViewFactory { get; }

        void IMessageBoxDetailViewControl.ShowMessageBoxContentView(
            MessageBoxViewController viewController,
            MessageBoxDetailViewModel viewModel,
            Action<MessageBoxViewModel, bool, bool> onCompleted,
            Action onMessageExpired)
        {
            if (viewModel.MessageFormatType == MessageFormatType.HasReward)
            {
                ShowMessageBoxDetailWithRewardView(viewController, viewModel, onCompleted, onMessageExpired);
            }
            else
            {
                ShowMessageBoxDetailView(viewController, viewModel, onCompleted, onMessageExpired);
            }
        }

        void ShowMessageBoxDetailWithRewardView(
            MessageBoxViewController viewController,
            MessageBoxDetailViewModel viewModel,
            Action<MessageBoxViewModel, bool, bool> onReceiveCompleted,
            Action onReceiveExpired)
        {
            var argument = new MessageBoxDetailWithRewardViewController.Argument(viewModel);
            var controller = ViewFactory.Create<
                MessageBoxDetailWithRewardViewController,
                MessageBoxDetailWithRewardViewController.Argument>(argument);

            controller.OnReceiveCompleted = onReceiveCompleted;
            controller.OnReceiveExpired = onReceiveExpired;
            viewController.PresentModally(controller);
        }

        void ShowMessageBoxDetailView(
            MessageBoxViewController viewController,
            MessageBoxDetailViewModel viewModel,
            Action<MessageBoxViewModel, bool, bool> onOpenCompleted,
            Action onOpenExpired)
        {
            var argument = new MessageBoxDetailViewController.Argument(viewModel);
            var controller = ViewFactory.Create<
                MessageBoxDetailViewController,
                MessageBoxDetailViewController.Argument>(argument);

            controller.OnOpenCompleted = onOpenCompleted;
            controller.OnOpenExpired = onOpenExpired;
            viewController.PresentModally(controller);
        }
    }
}
