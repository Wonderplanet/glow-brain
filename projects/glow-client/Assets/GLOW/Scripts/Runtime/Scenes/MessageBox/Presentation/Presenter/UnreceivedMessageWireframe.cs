using System;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.MessageBox.Presentation.Presenter;
using Zenject;

namespace GLOW.Scripts.Runtime.Scenes.MessageBox.Presentation.Presenter
{
    public class UnreceivedMessageWireframe : IUnreceivedMessageWireframe
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        
        void IUnreceivedMessageWireframe.ShowUnreceivedExpiredMessageView(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "期限切れのメールがあったため、受け取りできませんでした。\nもう一度お試しください。",
                onClose: onClose);
        }

        void IUnreceivedMessageWireframe.ShowUnopenedExpiredMessageView(Action onClose)
        {
            // 期限切れのメッセージがある場合は受け取れない
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "期限切れのメールがあったため、既読にできませんでした。\nもう一度お試しください。",
                onClose: onClose);
        }

        void IUnreceivedMessageWireframe.ShowUnreceivedLimitAmountMessageView(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "アイテムの所持上限を超えるため、受け取れないメールがありました。",
                onClose: onClose);
        }
    }
}