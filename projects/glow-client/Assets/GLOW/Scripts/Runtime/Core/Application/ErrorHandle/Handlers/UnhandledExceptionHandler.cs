using System;
using GLOW.Core.Domain.Modules.Network;
using GLOW.Modules.MessageView.Presentation;
using WPFramework.Application.ErrorHandle;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public sealed class UnhandledExceptionHandler : IUnhandledExceptionHandler
    {
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] ICommonExceptionViewer CommonExceptionViewer { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        bool IUnhandledExceptionHandler.Handle(Exception exception, Action completion)
        {
            // ネットワークエラーの判定
            if (IsNetworkError(exception))
            {
                MessageViewUtil.ShowMessageWithOk(
                    "通信エラー",
                    "通信環境の良いところで\n再度お試しください。",
                    string.Empty,
                    () =>
                    {
                        ApplicationRebootor.Reboot();
                        completion?.Invoke();
                    });
            }
            else
            {
                CommonExceptionViewer.Show(
                    Terms.Get("common_error_dialog_title"),
                    Terms.Get("common_error_dialog_message"),
                    exception,
                    completion);
            }

            return true;
        }

        bool IsNetworkError(Exception exception)
        {
            if (exception == null) return false;

            return NetworkErrorExceptionMessage.IsNetworkErrorExceptionMessage(exception.Message);
        }
    }
}
