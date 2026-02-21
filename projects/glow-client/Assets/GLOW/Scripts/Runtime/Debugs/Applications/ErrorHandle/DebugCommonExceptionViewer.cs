using System;
using System.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Application.ErrorHandle;
using GLOW.Core.Exceptions.CodeConversions;
using GLOW.Modules.MessageView.Presentation;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
#if GLOW_DEBUG
using GLOW.Debugs.Reporter;
using WonderPlanet.ToastNotifier;
#endif // GLOW_DEBUG
using Zenject;

namespace GLOW.Debugs.Applications.ErrorHandle
{
#if GLOW_DEBUG
    public class DebugCommonExceptionViewer : ICommonExceptionViewer
    {
        const string PrefabName = "DebugStackTraceMessageView";

        enum ActionTypes
        {
            Reboot,
            Ignore,
            Send,
        }

        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] IReporter Reporter { get; }

        void ICommonExceptionViewer.Show(string title, string message, Exception exception, Action completion)
        {
            var inquiryId = ExceptionConverter.ConvertToInquiryId(exception);
            var formattedMessage =
                Terms.Get("common_error_dialog_message_inquiry_format", message, inquiryId);

            // NOTE: エラーが発生した場合ダイアログを表示させてタイトルへ戻す
            //       詳細コントロールが必要であればExceptionのタイプなどで判別すること
            var builder = new StringBuilder();
            builder.AppendLine(formattedMessage);
            builder.AppendLine("--------------------");
            builder.AppendLine(ExceptionLog.BuildStackTrace(exception));

            DoAsync.Invoke(cancellationToken: default, async (cancellationToken) =>
            {
                await Reporter.Capture(cancellationToken);
                await UniTask.Yield(PlayerLoopTiming.LastPostLateUpdate);

                var completionSource = new UniTaskCompletionSource<ActionTypes>();
                // NOTE: Mesh can not have more than 65000 vertices のエラーが発生すると追えなくなるので切り出し
                //       少し余裕を持たせておく
                var maxStackTraceLength = 63000 / 4;
                var stackTrace = builder.ToString().Substring(0, Math.Min(builder.Length, maxStackTraceLength));
                MessageViewUtil.ShowMessageWith3Buttons(
                    title,
                    stackTrace,
                    string.Empty,
                    Terms.Get("common_error_dialog_button_title"),
                    Terms.Get("common_error_dialog_button_error_ignore"),
                    "Slackログ送信",
                    () => completionSource.TrySetResult(ActionTypes.Reboot),
                    () => completionSource.TrySetResult(ActionTypes.Ignore),
                    () => completionSource.TrySetResult(ActionTypes.Send),
                    () => completionSource.TrySetResult(ActionTypes.Ignore),
                    false,
                    PrefabName);

                var type = await completionSource.Task;
                switch (type)
                {
                    case ActionTypes.Reboot:
                        ApplicationRebootor.Reboot();
                        break;
                    case ActionTypes.Send:
                        Toast.MakeText("Slackログ送信開始").Show();
                        await Reporter.Send(cancellationToken);
                        Toast.MakeText("Slackログ送信完了").Show();
                        break;
                    case ActionTypes.Ignore:
                        break;
                    default:
                        throw new ArgumentOutOfRangeException();
                }
                completion?.Invoke();
            });
        }
    }
#endif // GLOW_DEBUG
}
