using System;
using GLOW.Modules.MessageView.Presentation;
using WPFramework.Application.ErrorHandle;
using WPFramework.Modules.Localization.Terms;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public class TimeoutControlHandler : ITimeoutControlHandler
    {
        [Inject] IMessageViewUtil AlertUtil { get; }
        [Inject] ILocalizationTermsSource Terms { get; }

        bool ITimeoutControlHandler.Handle(Action retry, Action abort)
        {
            AlertUtil.ShowMessageWith2Buttons(
                Terms.Get("common_network_error_dialog_time_out_title"),
                Terms.Get("common_network_error_dialog_retry_message"),
                string.Empty,
                Terms.Get("common_ok"),
                Terms.Get("common_error_dialog_button_title"),
                retry,
                abort,
                abort,
                true);

            return true;
        }
    }
}
