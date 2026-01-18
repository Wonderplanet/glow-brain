using System;
using GLOW.Modules.MessageView.Presentation;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Presentation.WireFrame
{
    public class BoxGachaExceptionWireFrame
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        
        public void ShowMessageCostNotEnough(Action onCompleted = null)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "いいじゃんガチャ開催期間は終了しました。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                });
        }
        
        public void ShowMessageDrawCountExceeded(Action onCompleted = null)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "いいジャンくじのラインナップが不整合です。\nタイトルへ戻ります。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                });
        }
        
        public void ShowMessageAfterBoxGachaPeriod(Action onCompleted = null)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "いいジャンくじの開催期間は終了しました。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                });
        }
        
        public void ShowMessageStockNotEnough(Action onCompleted = null)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "今のいいジャンくじはもう引けません。\nくじをリセットしてください。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                });
        }
        
        public void ShowMessageItemAmountIsNotEnough(Action onCompleted = null)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "いいジャンくじを引くためのアイテムが不足しています。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                });
        }
    }
}