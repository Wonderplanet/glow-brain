using System;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaTrade
{
    public class StaminaTradeConfirmWireFrame
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        public void BackToHomeTopUserStaminaFull(Action onCompleted)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "スタミナが上限に達しています。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                    TransitHomeTop();
                });
        }

        public void BackToHomeTopItemNotOwned(Action onCompleted)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "使用しようとしたアイテムを所持していません。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                    TransitHomeTop();
                });
        }

        public void BackToHomeTopStaminaExceedsLimit(Action onCompleted)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "スタミナの回復上限を超えています。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                    TransitHomeTop();
                });
        }

        public void BackToHomeTopItemNotFound(Action onCompleted)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "使用しようとしたアイテムが見つかりません。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    onCompleted?.Invoke();
                    TransitHomeTop();
                });
        }

        public void BackToHomeTopInvalidParameter(Action onCompleted)
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "不正な値が入りました。\nホーム画面に移動します。",
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
