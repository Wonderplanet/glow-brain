using System;
using GLOW.Modules.MessageView.Presentation;
using Zenject;

namespace GLOW.Scenes.Shop.Presentation.Presenter
{
    public class DiamondPurchaseWireframe
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        public void ShowOutOfTimeDiamondPurchaseView(Action onClose)
        {
            //InAppPurchasePresenterのテキスト引用
            var title = "購入できません";
            var message = "購入に失敗しました。\n期限切れの商品です。";
            MessageViewUtil.ShowMessageWithClose(
                title,
                message,
                onClose: onClose
            );
        }
    }
}