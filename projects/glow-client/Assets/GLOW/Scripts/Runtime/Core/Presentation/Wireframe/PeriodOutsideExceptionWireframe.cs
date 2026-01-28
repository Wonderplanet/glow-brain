using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Exceptions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.BattleResult.Domain.Enum;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;
using Zenject;

namespace GLOW.Core.Presentation.Wireframe
{
    public interface IPeriodOutsideExceptionWireframe
    {
        void ShowPeriodOutsideExceptionMessage(CheckContentOpenModel model, Action onClose);
        void ShowPeriodOutsideExceptionMessage(Exception ex, Action onClose);
        void ShowForceAbortMessage(Action onClose, InGameContentType inGameContentType);
    }

    public class PeriodOutsideExceptionWireframe : IPeriodOutsideExceptionWireframe
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        public void ShowPeriodOutsideExceptionMessage(CheckContentOpenModel model, Action onClose)
        {
            Exception exception = model.InGameStageType switch
            {
                InGameStageType.NormalStage => 
                    new QuestPeriodOutsideException((int)ServerErrorCode.QuestPeriodOutside),
                InGameStageType.EventStage => 
                    new EventPeriodOutsideException((int)ServerErrorCode.EventPeriodOutside),
                InGameStageType.AdventBattle => 
                    new AdventBattlePeriodOutsideException((int)ServerErrorCode.AdventBattlePeriodOutside),
                InGameStageType.Pvp => 
                    new PvpPeriodOutsideException((int)ServerErrorCode.PvpPeriodOutside),
                _ => throw new InvalidOperationException($"対応外のInGameStageTypeが渡されました:{model.InGameStageType}")
            };

            ShowPeriodOutsideExceptionMessage(exception, onClose);
        }

        public void ShowPeriodOutsideExceptionMessage(Exception ex, Action onClose)
        {
            var message = ex switch
            {
                QuestPeriodOutsideException => "クエスト開催期間が終了しました。\nホーム画面に移動します。",
                EventPeriodOutsideException => "イベント開催期間が終了しました。\nホーム画面に移動します。",
                PvpPeriodOutsideException => "今シーズンのランクマッチの開催期間は\n終了しました。\n" +
                                             "今回のバトル結果は、\n今シーズンの結果には反映されません。\nホーム画面に移動します。",
                AdventBattlePeriodOutsideException => "降臨バトルの開催期間は終了しました。\n" +
                                                      "ホーム画面に移動します。",
                _ => throw new InvalidOperationException($"対応外のexceptionが渡されました:{ex.GetType()}", ex)
            };

            var attentionMessage = ex switch
            {
                QuestPeriodOutsideException => "消費したスタミナやプリズムは返却されません。",
                EventPeriodOutsideException => "消費したスタミナやプリズムは返却されません。",
                AdventBattlePeriodOutsideException => "今回のバトル結果は、ランキングやスコアには\n反映されません。\nまた、消費した挑戦回数は返却されません。 ",
                PvpPeriodOutsideException => "消費した挑戦回数やランクマッチチケットは\n返却されません。",
                _ => string.Empty
            };

            MessageViewUtil.ShowMessageWithOk(
                "確認",
                message,
                attentionMessage,
                onClose);
        }

        public void ShowForceAbortMessage(Action onClose, InGameContentType inGameContentType)
        {
            var message = inGameContentType switch
            {
                InGameContentType.Pvp => "今シーズンのランクマッチの開催期間は\n終了しました。\n" +
                                         "今回のバトル結果は、\n今シーズンの結果には反映されません。\nホーム画面に移動します。",
                InGameContentType.AdventBattle => "降臨バトルの開催期間は終了しました。\n" +
                                                  "今回のバトル結果は、ランキングや\n協力スコアには反映されません。\n" +
                                                  "ホーム画面に移動します。",
                _ => "開催期間を過ぎたため\n中断データは無効になりました。\n消費したスタミナやプリズムは\n返却されません。"
            };

            var attentionMessage = inGameContentType switch
            {
                InGameContentType.Pvp => "消費した挑戦回数やランクマッチチケットは\n返却されません。",
                _ => string.Empty
            };

            MessageViewUtil.ShowMessageWithClose(
                "バトル再開確認",
                message,
                attentionMessage,
                onClose
            );
        }
    }
}
