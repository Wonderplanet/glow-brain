using System;
using GLOW.Modules.MessageView.Presentation;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Presenters
{
    public class ActiveItemWireFrame
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        public void ShowInactiveItemMessage(UIViewController controller, Action onCompleted = null)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "このアイテムは現在使用できません。",
                "",
                () =>
                {
                    onCompleted?.Invoke();
                    controller.Dismiss();
                });
        }
    }
}
