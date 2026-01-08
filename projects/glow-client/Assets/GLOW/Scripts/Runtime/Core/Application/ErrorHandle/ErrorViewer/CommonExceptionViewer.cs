using System;
using GLOW.Core.Exceptions.CodeConversions;
using GLOW.Modules.MessageView.Presentation;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle
{
    public class CommonExceptionViewer : ICommonExceptionViewer
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }

        void ICommonExceptionViewer.Show(string title, string message, Exception exception, Action completion)
        {
            var inquiryId = ExceptionConverter.ConvertToInquiryId(exception);
            var formattedMessage =
                Terms.Get("common_error_dialog_message_inquiry_format", message, inquiryId);

            MessageViewUtil.ShowMessageWithButton(
                title,
                formattedMessage,
                string.Empty,
                Terms.Get("common_error_dialog_button_title"),
                () =>
                {
                    ApplicationRebootor.Reboot();
                    completion?.Invoke();
                },
                false);
        }
    }
}
