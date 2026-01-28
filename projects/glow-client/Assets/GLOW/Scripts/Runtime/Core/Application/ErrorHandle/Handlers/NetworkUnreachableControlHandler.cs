using System;
using GLOW.Modules.MessageView.Presentation;
using WPFramework.Application.ErrorHandle;
using WPFramework.Modules.Localization.Terms;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public sealed class NetworkUnreachableControlHandler : INetworkUnreachableControlHandler
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] ILocalizationTermsSource Terms { get; }

        public bool Handle(Action retry, Action abort)
        {
            MessageViewUtil.ShowMessageWith2Buttons(
                Terms.Get("common_network_error_dialog_unreachable_title"),
                Terms.Get("common_network_error_dialog_unreachable_message"),
                string.Empty,
                Terms.Get("common_error_dialog_button_retry"),
                Terms.Get("common_error_dialog_button_title"),
                retry,
                abort,
                abort,
                true);

            return true;
        }
    }
}
