using System;
using GLOW.Modules.MessageView.Presentation;
using Zenject;

namespace GLOW.Scenes.PassShop.Presentation.Presenter
{
    public class PassExceptionMessageWireframe : IPassExceptionMessageWireframe
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        
        public void ShowExpiredPassPurchaseErrorMessage(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "このパスの販売は終了しました。",
                onClose: onClose);
        }

        public void ShowAlreadyPurchasedPassMessage(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "このパスは既に購入されています。",
                onClose: onClose);
        }
    }
}