using System;
using UnityHTTPLibrary;
using WPFramework.Application.ErrorHandle;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public sealed class NetworkExceptionHandler : INetworkExceptionHandler
    {
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] ICommonExceptionViewer CommonExceptionViewer { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }

        bool INetworkExceptionHandler.Handle(NetworkException exception, Action completion)
        {
            switch (exception)
            {
                case InternetNotReachableException:
                // NOTE: IServerErrorDelegate.OnNetworkUnreachable で既にハンドリングされている
                case NetworkTimeoutException:
                    // NOTE: ITimeOutDelegate.OnTimeOut で既にハンドリングされている
                    ApplicationRebootor.Reboot();

                    completion?.Invoke();
                    return true;
            }

            CommonExceptionViewer.Show(
                Terms.Get("common_network_error_dialog_title"),
                Terms.Get("common_error_dialog_message"),
                exception,
                completion);

            return true;
        }
    }
}
