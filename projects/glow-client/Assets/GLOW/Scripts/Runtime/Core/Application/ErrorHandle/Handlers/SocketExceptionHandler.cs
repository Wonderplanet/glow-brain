using System;
using System.Net.Sockets;
using WPFramework.Application.ErrorHandle;
using WPFramework.Modules.Localization.Terms;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public class SocketExceptionHandler : ISocketExceptionHandler
    {
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] ICommonExceptionViewer CommonExceptionViewer { get; }

        bool ISocketExceptionHandler.Handle(SocketException exception, Action completion)
        {
            CommonExceptionViewer.Show(
                Terms.Get("common_network_error_dialog_title"),
                Terms.Get("common_error_dialog_message"),
                exception,
                completion);

            return true;
        }
    }
}
