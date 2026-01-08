using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using UnityHTTPLibrary;
using UnityHTTPLibrary.UnityWebRequestImpl;
using UnityHTTPLibrary.Utility;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using UnityWebRequest = UnityEngine.Networking.UnityWebRequest;

namespace WPFramework.Modules.Environment
{
    public sealed class NetworkEnvironmentProtocol : IEnvironmentProtocol
    {
        public record Settings(string FixedIdentifier, string EnvironmentListFileExtension, string EnvironmentHost, string EnvironmentPath, string ApplicationVersion)
        {
            public string FixedIdentifier { get; } = FixedIdentifier;
            public string EnvironmentListFileExtension { get; } = EnvironmentListFileExtension;
            public string EnvironmentHost { get; } = EnvironmentHost;
            public string EnvironmentPath { get; } = EnvironmentPath;
            public string ApplicationVersion { get; } = ApplicationVersion;
        }

        const int RetryWaitSeconds = 1;

        int IEnvironmentProtocol.Priority => 999;

        readonly CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        readonly IEnvironmentDataParser _environmentDataParser;
        readonly ITLSCertificateHandler _certificateHandler;
        readonly INetworkEnvironmentErrorHandler _errorHandler;

        readonly Settings _settings;

        bool _isDisposed;

        public NetworkEnvironmentProtocol(Settings settings, IEnvironmentDataParser environmentDataParser, ITLSCertificateHandler certificateHandler, INetworkEnvironmentErrorHandler errorHandler = null)
        {
            _settings = settings;
            _environmentDataParser = environmentDataParser;
            _certificateHandler = certificateHandler;
            _errorHandler = errorHandler;
        }

        async UniTask<EnvironmentListData> IEnvironmentProtocol.FetchEnvironmentList(CancellationToken cancellationToken)
        {
            // NOTE: エラーハンドラーが設定されている場合はダイアログ表示、そうでなければ従来通りの自動リトライ
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);
            
            if (_errorHandler != null)
            {
                return await FetchWithErrorHandling(cts.Token);
            }
            
            return await FetchWithAutoRetry(cts.Token);
        }

        async UniTask<EnvironmentListData> FetchWithErrorHandling(CancellationToken cancellationToken)
        {
            // ダイアログ表示によるユーザー選択リトライ
            while (!cancellationToken.IsCancellationRequested)
            {
                var result = await FetchEnvironmentListInternal(cancellationToken);
                if (result.success)
                {
                    return result.data;
                }

                // エラーダイアログを表示してリトライするか確認
                var shouldRetry = await _errorHandler.HandleNetworkError(cancellationToken);
                if (!shouldRetry)
                {
                    throw new OperationCanceledException("Environment file download was cancelled by user.");
                }
            }
            
            throw new OperationCanceledException();
        }

        async UniTask<EnvironmentListData> FetchWithAutoRetry(CancellationToken cancellationToken)
        {
            // 従来通りの自動リトライ
            return await TaskRunner.Retryable(
                cancellationToken,
                async (token, _) =>
                {
                    var result = await FetchEnvironmentListInternal(token);
                    if (!result.success)
                    {
                        await WaitForRetryable(token);
                        throw new TaskRetryableRequestedException();
                    }
                    return result.data;
                });
        }

        async UniTask<(bool success, EnvironmentListData data)> FetchEnvironmentListInternal(CancellationToken cancellationToken)
        {
            UnityWebRequest request = null;

            try
            {
                if (Application.internetReachability == NetworkReachability.NotReachable)
                {
                    ApplicationLog.LogWarning(nameof(NetworkEnvironmentProtocol),
                        "FetchEnvironmentList: No Internet Connection");
                    return (false, null);
                }

                var resolver = new NetworkEnvironmentPathResolver(
                    applicationVersion: _settings.ApplicationVersion,
                    fixedIdentifier: _settings.FixedIdentifier,
                    relativePath: _settings.EnvironmentPath,
                    fileExtension: _settings.EnvironmentListFileExtension);
                var uri =
                    new Uri(new Uri(_settings.EnvironmentHost), new Uri(resolver.Resolve(), UriKind.Relative));
                ApplicationLog.Log(nameof(NetworkEnvironmentProtocol), $"FetchEnvironmentList: {uri}");
                // NOTE: 独立で動作させるようにUnityWebRequestを使用する
                request = UnityWebRequest.Get(uri);
                // NOTE: TLSの検証処理を設定する
                if (_certificateHandler != null)
                {
                    request.certificateHandler =
                        new UnityWebRequestCertificateHandler(uri.Host, _certificateHandler);
                }

                request.timeout = 5;
                await request.SendWebRequest().ToUniTask(cancellationToken: cancellationToken);

                var text = request.downloadHandler.text;
                ApplicationLog.Log(nameof(NetworkEnvironmentProtocol), $"FetchEnvironmentList: {text}");
                var environmentListData = _environmentDataParser.Parse<EnvironmentListData>(text);
                return (true, environmentListData);
            }
            catch (UnityWebRequestException e)
            {
                // NOTE: 証明書検証に失敗した場合どうしようもなくなるのでエラーを発生させる
                if (request != null && UnityWebRequestUtility.IsTLSCertificateError(request))
                {
                    throw new TLSCertificateValidationException(request.error);
                }

                ApplicationLog.LogWarning(nameof(NetworkEnvironmentProtocol), $"FetchEnvironmentList: {e}");
                return (false, null);
            }
            finally
            {
                request?.Dispose();
            }
        }

        async UniTask WaitForRetryable(CancellationToken cancellationToken)
        {
            await UniTask.Delay(TimeSpan.FromSeconds(RetryWaitSeconds), cancellationToken: cancellationToken);
        }

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            _cancellationTokenSource.Cancel();
            _cancellationTokenSource.Dispose();
        }
    }
}
