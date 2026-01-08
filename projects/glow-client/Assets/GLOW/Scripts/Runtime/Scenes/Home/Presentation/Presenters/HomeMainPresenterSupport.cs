using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class HomeMainPresenterSupport
    {
        public StageRequireStatus CheckStageAvailable(
            bool isReleased,
            StageConsumeStamina consumeStamina,
            Stamina currentStamina,
            IReadOnlyList<StageLimitStatusViewModel> invalidPartyViewModels,
            DateTimeOffset now,
            UnlimitedCalculableDateTimeOffset targetStageEndAt,
            StageClearCount dailyClearCount,
            ClearableCount dailyPlayableCount)
        {
            if (!isReleased) return StageRequireStatus.UnRelease;
            if (currentStamina.Value < consumeStamina.Value) return StageRequireStatus.StaminaLack;
            if (1 <= invalidPartyViewModels.Count) return StageRequireStatus.InvalidParty;
            if (targetStageEndAt < now) return StageRequireStatus.OutOfAvailableTime;
            if (!dailyPlayableCount.IsEmpty() && dailyPlayableCount <= dailyClearCount) return StageRequireStatus.LimitDailyClear;
            else return StageRequireStatus.Nothing;
        }

        //表示文字を分散させたくないので、ここのシートにまとめる
        public void ShowStageUnReleaseMessage(IMessageViewUtil util)
        {
            util.ShowMessageWithClose(
                "確認",
                "以下の理由によりステージ選択できません。",
                "【未開放ステージ】",
                () => { });
        }
        public void ShowStageUnReleaseOutOfTimeMessage(
            IMessageViewUtil util,
            Action onClose = null)
        {
            util.ShowMessageWithClose("確認",
                "開催期間が終了しています。\n次回開催をお待ちください。",
                "",
                () =>
                {
                    onClose?.Invoke();
                });
        }
        public void ShowLimitDailyClearCountMessage(IMessageViewUtil util)
        {
            util.ShowMessageWithClose(
                "確認",
                "以下の理由によりステージ選択できません。",
                "【挑戦回数超過】",
                () => { });
        }
    }
}
