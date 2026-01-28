using System;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Presentation.WireFrame
{
    public class ExchangeConfirmWireFrame
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        public void BackToHomeTopLineupMismatch(Action onCompleted)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "選択された交換商品は現在交換できません。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                    TransitHomeTop();
                });
        }

        public void BackToHomeTopLimitExceeded(Action onCompleted)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "交換上限数に達しています。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                    TransitHomeTop();
                });
        }

        public void BackToHomeTopAfterTradePeriod(Action onCompleted)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "交換期限は終了しました。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                    TransitHomeTop();
                });
        }

        public void BackToHomeTopShortageItem(Action onCompleted)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "交換に必要なアイテムが不足しています。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                    TransitHomeTop();
                });
        }

        void TransitHomeTop()
        {
            if (HomeViewNavigation.CurrentContentType == HomeContentTypes.Main)
            {
                HomeViewNavigation.TryPopToRoot();
            }
            else
            {
                HomeViewNavigation.Switch(HomeContentTypes.Main);
            }
        }
    }
}
