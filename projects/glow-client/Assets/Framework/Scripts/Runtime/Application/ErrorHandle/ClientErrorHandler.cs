using System;
using System.Net.Sockets;
using UnityEngine.AddressableAssets;
using UnityHTTPLibrary;
using UnityHTTPLibrary.Authenticate.Exceptions;
using WonderPlanet.CrashReporterBridge;
using WonderPlanet.ErrorCoordinator;
using WonderPlanet.ToastNotifier;
using WPFramework.Exceptions;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Modules.Log;
using Zenject;

namespace WPFramework.Application.ErrorHandle
{
    public sealed class ClientErrorHandler : IErrorHandler, IServerErrorDelegate, ITimeOutDelegate
    {
        [Inject] IUnhandledExceptionHandler UnhandledExceptionHandler { get; }
        [Inject] ITimeoutControlHandler TimeoutControlHandler { get; }
        [Inject] IServerErrorExceptionPreHandler ServerErrorExceptionPreHandler { get; }
        [Inject] IServerErrorExceptionPostHandler ServerErrorExceptionPostHandler { get; }
        [Inject] INetworkExceptionHandler NetworkExceptionHandler { get; }
        [Inject] ISocketExceptionHandler SocketExceptionHandler { get; }
        [Inject] IDiskFullExceptionHandler DiskFullExceptionHandler { get; }
        [Inject] IAuthenticatorExceptionHandler AuthenticatorExceptionHandler { get; }
        [Inject] INetworkUnreachableControlHandler NetworkUnreachableControlHandler { get; }
        [Inject] IAddressablesInvalidKeyExceptionHandler AddressablesInvalidKeyExceptionHandler { get; }
        [Inject] CrashReportCenter CrashReportCenter { get; }
        [Inject] ILocalizationTermsSource Terms { get; }

        bool _isDisposed;
        bool _isExecutingErrorHandling;

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            _isExecutingErrorHandling = false;
        }

        bool IErrorHandler.OnLogMessage(ILogInfo info) => true; // NOTE: メッセージによるハンドリングは行わない

        /// <summary>
        /// Debug.LogError()をハンドリング
        /// </summary>
        /// <param name="info">ログ情報</param>
        /// <returns><c>true</c>ハンドリング済み <c>false</c>未ハンドリングにより後続のハンドラーへ通知を送る</returns>
        bool IErrorHandler.OnLogErrorMessage(ILogInfo info)
        {
            // NOTE: ログをToastで表示するのはデバッグ時のみ
#if UNITY_EDITOR || DEBUG
            Toast.MakeText(string.Format(info.Format, info.Args))?.Show();
#endif  // UNITY_EDITOR || DEBUG
            return true;
        }

        /// <summary>
        /// Exception系をハンドリング
        /// </summary>
        /// <param name="info">ログ情報</param>
        /// <returns><c>true</c>ハンドリング済み <c>false</c>未ハンドリングにより後続のハンドラーへ通知を送る</returns>
        bool IErrorHandler.OnLogException(ILogInfo info)
        {
            try
            {
                if (_isExecutingErrorHandling)
                {
                    ApplicationLog.LogWarning(nameof(ClientErrorHandler), $"Already Executing Error Handling: {info.Exception}");
                    return false;
                }

                _isExecutingErrorHandling = true;

                switch (info.Exception)
                {
                    case AuthenticatorException ae:
                        if (AuthenticatorExceptionHandler.Handle(ae, () => { _isExecutingErrorHandling = false; }))
                        {
                            return true;
                        }
                        break;
                    case ServerErrorException see:
                        if (ServerErrorExceptionPostHandler.Handle(see, () => { _isExecutingErrorHandling = false; }))
                        {
                            return true;
                        }
                        break;
                    case NetworkException ne:
                        if (NetworkExceptionHandler.Handle(ne, () => { _isExecutingErrorHandling = false; }))
                        {
                            return true;
                        }
                        break;
                    case SocketException se:
                        if (SocketExceptionHandler.Handle(se, () => { _isExecutingErrorHandling = false; }))
                        {
                            return true;
                        }
                        break;
                    case DiskFullException dfe:
                        if (DiskFullExceptionHandler.Handle(dfe, () => { _isExecutingErrorHandling = false; }))
                        {
                            return true;
                        }
                        break;
                    case InvalidKeyException ike:
                        if (AddressablesInvalidKeyExceptionHandler.Handle(ike, () => { _isExecutingErrorHandling = false; }))
                        {
                            return true;
                        }
                        break;
                }

                return UnhandledExceptionHandler.Handle(info.Exception, () => { _isExecutingErrorHandling = false;});
            }
            catch (Exception e)
            {
                CrashReportCenter.LogException(e);

                // NOTE: ここで発生したエラーは対処不可能なためスルーする
                _isExecutingErrorHandling = false;
                ApplicationLog.LogWarning(nameof(ClientErrorHandler), $"Unhandled Exception: {e}");
            }

            return true;
        }

        bool IServerErrorDelegate.OnServerError(Exception exception, object optionalResult)
        {
            // NOTE: サーバーエラーはServerErrorExceptionとしてハンドリングする
            if (exception is not ServerErrorException serverErrorException)
            {
                return false;
            }

            if (_isExecutingErrorHandling)
            {
                ApplicationLog.LogWarning(nameof(ClientErrorHandler), $"Already Executing Error Handling: {exception}");
                return false;
            }

            _isExecutingErrorHandling = true;

            try
            {
                // NOTE: サーバーエラーのハンドリングは2段階で行う
                //       PreHandlerでハンドリングできるものはそこでハンドリングし、PreHandlerでハンドリングできないものはPostHandlerでハンドリングする
                //       PreHandlerでハンドリングされた場合はLogExceptionへ通知が送られない
                return ServerErrorExceptionPreHandler.Handle(serverErrorException, () => { _isExecutingErrorHandling = false; });
            }
            catch (Exception e)
            {
                CrashReportCenter.LogException(e);

                // NOTE: ここで発生したエラーは対処不可能なためスルーする
                _isExecutingErrorHandling = false;
                ApplicationLog.LogWarning(nameof(ClientErrorHandler), $"Unhandled Exception: {e}");
            }

            return false;
        }

        void IServerErrorDelegate.OnNetworkUnreachable(Uri uri, IRetryEventHandler eventHandler, object optionalResult)
        {
            // NOTE: タイムアウト時のダイアログを表示する
            //       キャンセルを行った場合は `InternetNotReachableException` が発生する
            //       その際に`OnLogException` が呼ばれるため、ここではリトライかアボートの選択を促す
            NetworkUnreachableControlHandler.Handle(eventHandler.RetryNetworkUnreachableRequests, eventHandler.AbortNetworkUnreachableRequests);
        }

        void ITimeOutDelegate.OnTimeOut(Uri uri, IRetryEventHandler eventHandler, object optionalResult)
        {
            // NOTE: タイムアウト時のダイアログを表示する
            //       キャンセルを行った場合は `NetworkTimeoutException` が発生する
            //       その際に`OnLogException` が呼ばれるため、ここではリトライかアボートの選択を促す
            TimeoutControlHandler.Handle(eventHandler.RetryTimeoutRequests, eventHandler.AbortTimeoutRequests);
        }
    }
}
