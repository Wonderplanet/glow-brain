using System;
using GLOW.Modules.MessageView.Presentation;
using Zenject;

namespace GLOW.Scenes.Mission.Presentation.Presenter
{
    public class UnreceivedRewardWireframe
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        //reason単位で本文を追加して、モーダル自体は1枚だけ出す、という形にしてもよいかも
        public void ShowSentToMailbox(Action onClose = null)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "所持上限を超えるため、\n報酬をメールBOXに送りました。",
                onClose: onClose
            );
        }
        public void ShowResourceLimitReached(Action onClose = null)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "所持上限に達しているため、\n受け取ることができませんでした。",
                onClose: onClose
            );
        }
        public void ShowResourceOverflowDiscarded(Action onClose = null)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "所持上限に達しているため、\n受け取ることができませんでした。",
                onClose: onClose
            );
        }
    }
}
