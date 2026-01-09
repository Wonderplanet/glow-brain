using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityHTTPLibrary;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;

namespace WPFramework.Data.DataStores
{
    internal sealed class RemoteSyncProcedure<T> : IDisposable where T : RemoteSyncData
    {
        readonly CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();

        bool _isDisposed;

        CancellationTokenSource _syncCancellationTokenSource;

        readonly IRemoteSyncExecutor<T> _executor;
        readonly IRemoteSyncDataErrorHandler _dataErrorHandler;
        readonly IRemoteSyncRetryDelayProtocol _retryDelayProtocol;

        public RemoteSyncProcedure(IRemoteSyncExecutor<T> executor, IRemoteSyncDataErrorHandler dataErrorHandler, IRemoteSyncRetryDelayProtocol retryDelayProtocol)
        {
            _executor = executor;
            _dataErrorHandler = dataErrorHandler;
            _retryDelayProtocol = retryDelayProtocol;
        }

        public async UniTask Sync(CancellationToken cancellationToken, RemoteSyncTargetData<T>[] data)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncProcedure<T>));
            }

            // NOTE: 同期中に新しい同期依頼が来た場合に現在の同期処理をキャンセルさせる
            _syncCancellationTokenSource?.Cancel();
            _syncCancellationTokenSource?.Dispose();

            _syncCancellationTokenSource =
                CancellationTokenSource.CreateLinkedTokenSource(
                    _cancellationTokenSource.Token,
                    cancellationToken);

            // NOTE: 通信が成功するまで基本的にリトライをするようにする
            await TaskRunner.Retryable(_syncCancellationTokenSource.Token, async (ct, count) =>
            {
                try
                {
                    // NOTE: 同期処理を実行する
                    await _executor.Sync(ct, data);

                    DisposeSyncToken();
                }
                catch (OperationCanceledException)
                {
                    // NOTE: キャンセルされた場合は素直に終了させる
                    ApplicationLog.Log(nameof(RemoteSyncProcedure<T>), $"Sync canceled.");

                    DisposeSyncToken();

                    throw new OperationCanceledException();
                }
                catch (NetworkTimeoutException nte)
                {
                    // NOTE: ハンドリングしているならばOperationCanceledExceptionを投げる
                    if (_dataErrorHandler?.OnRemoteSyncFailed(typeof(T), nte) ?? false)
                    {
                        DisposeSyncToken();

                        throw new OperationCanceledException(nte.Message, nte);
                    }

                    // NOTE: リトライ前に一度待機する
                    await WaitForRetryable(ct, count);

                    throw new TaskRetryableRequestedException();
                }
                catch (InternetNotReachableException ire)
                {
                    // NOTE: ハンドリングしているならばOperationCanceledExceptionを投げる
                    if (_dataErrorHandler?.OnRemoteSyncFailed(typeof(T), ire) ?? false)
                    {
                        DisposeSyncToken();

                        throw new OperationCanceledException(ire.Message, ire);
                    }

                    // NOTE: リトライ前に一度待機する
                    await WaitForRetryable(ct, count);

                    throw new TaskRetryableRequestedException();
                }
                catch (ServerErrorException see)
                {
                    // NOTE: ハンドリングされていた場合はOperationCanceledExceptionを投げる
                    if (_dataErrorHandler?.OnRemoteSyncFailed(typeof(T), see) ?? false)
                    {
                        DisposeSyncToken();

                        throw new OperationCanceledException(see.Message, see);
                    }

                    // NOTE: リトライ前に一度待機する
                    await WaitForRetryable(ct, count);

                    // NOTE: ハンドリングしていない場合はそのまま例外を投げる
                    throw new TaskRetryableRequestedException();
                }
                catch (NetworkException ne)
                {
                    // NOTE: ハンドリングしているならばOperationCanceledExceptionを投げる
                    if (_dataErrorHandler?.OnRemoteSyncFailed(typeof(T), ne) ?? false)
                    {
                        DisposeSyncToken();

                        throw new OperationCanceledException(ne.Message, ne);
                    }

                    // NOTE: リトライ前に一度待機する
                    await WaitForRetryable(ct, count);

                    throw new TaskRetryableRequestedException();
                }
                catch(Exception e)
                {
                    // NOTE: ハンドリングしているならばOperationCanceledExceptionを投げる
                    if (_dataErrorHandler?.OnRemoteSyncFailed(typeof(T), e) ?? false)
                    {
                        DisposeSyncToken();

                        throw new OperationCanceledException(e.Message, e);
                    }

                    // NOTE: リトライ前に一度待機する
                    await WaitForRetryable(ct, count);

                    throw new TaskRetryableRequestedException();
                }
            });
        }

        async UniTask WaitForRetryable(CancellationToken cancellationToken, int count)
        {
            if (_retryDelayProtocol == null)
            {
                await UniTask.CompletedTask;
            }
            else
            {
                var retryInterval = _retryDelayProtocol.CalculateDelayTime(count);
                ApplicationLog.Log(nameof(RemoteSyncProcedure<T>), $"Retry after {retryInterval.Seconds} seconds.");
                await UniTask.Delay(_retryDelayProtocol.CalculateDelayTime(count), cancellationToken: cancellationToken);
            }
        }

        public async UniTask WaitForCompletion(CancellationToken cancellationToken, TimeSpan timeout)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            // NOTE: キューが全て消化されていることを確認してから完了を待つ
            using var cts =
                CancellationTokenSource.CreateLinkedTokenSource(
                    _cancellationTokenSource.Token,
                    cancellationToken);
            // NOTE: キャンセルか通信が成功しないと無限待ちが発生する可能性があるのでタイムアウトをさせる
            cts.CancelAfter(timeout);

            try
            {
                await UniTask.WaitUntil(() => _syncCancellationTokenSource == null, cancellationToken: cts.Token);
            }
            catch (OperationCanceledException oce) when (oce.CancellationToken == cts.Token)
            {
                // NOTE: 外部から渡されてきたCancellationTokenがキャンセルされた場合は例外を投げる
                if (cancellationToken.IsCancellationRequested)
                {
                    throw new OperationCanceledException(oce.Message, oce, cancellationToken);
                }

                // NOTE: 内部でキャンセルされた場合は例外を投げる
                if (_cancellationTokenSource.IsCancellationRequested)
                {
                    throw new OperationCanceledException(oce.Message, oce, _cancellationTokenSource.Token);
                }

                // NOTE: タイムアウトした場合は例外を投げる
                throw new TimeoutException(message: "WaitForCompletion timeout.");
            }
        }

        void DisposeSyncToken()
        {
            _syncCancellationTokenSource?.Dispose();
            _syncCancellationTokenSource = null;
        }

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            _syncCancellationTokenSource?.Cancel();
            _syncCancellationTokenSource?.Dispose();
            _syncCancellationTokenSource = null;

            _cancellationTokenSource.Cancel();
            _cancellationTokenSource.Dispose();
        }
    }
}
