using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.Polling
{
    public sealed class PollingManager : IDisposable
    {
        readonly List<PollingOperation> _pollingOperations = new ();

        readonly CancellationTokenSource _cancellationTokenSource = new ();

        bool IsDisposed { get; set; } = false;

        public IPollingOperable Register(
            IPollingTask pollingTask,
            TimeSpan interval,
            CancellationToken cancellationToken = default,
            bool isExecutionDelayed = false
            )
        {
            if (IsDisposed)
            {
                ApplicationLog.Log(nameof(PollingManager), $"Register: already disposed");
                return null;
            }

            var operation = new PollingOperation(
                pollingManager: this,
                pollingTask: pollingTask,
                interval: interval,
                cancellationToken: cancellationToken,
                isExecutionDelayed: isExecutionDelayed
                );

            _pollingOperations.Add(operation);

            return operation;
        }

        public void Dispose()
        {
            if (IsDisposed)
            {
                ApplicationLog.Log(nameof(PollingManager), $"Register: already disposed");
                return;
            }
            IsDisposed = true;

            // NOTE: 中でUnregister()が呼ばれる為、ローカル変数にしてからforを回す
            foreach (var operation in _pollingOperations.ToArray())
            {
                operation.Dispose();
            }
            _pollingOperations.Clear();

            _cancellationTokenSource.Cancel();
            _cancellationTokenSource.Dispose();
        }

        internal void Unregister(PollingOperation operation)
        {
            _pollingOperations.Remove(operation);
        }

        internal void StartPolling(PollingOperation operation)
        {
            var cancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                _cancellationTokenSource.Token,
                operation.CancellationTokenByStop,
                operation.CancellationTokenByRegister
            );

            DoAsync.Invoke(cancellationToken.Token, async cancellationToken =>
            {
                operation.PollingTask.OnInitialize();

                // NOTE: 待ってから実行する場合
                if (operation.IsExecutionDelayed)
                {
                    await UniTask.Delay(operation.Interval, cancellationToken: cancellationToken);
                }

                await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
                {
                    try
                    {
                        operation.PollingTask.OnStarted();
                        await operation.PollingTask.OnExecute(cancellationToken);
                        operation.PollingTask.OnFinished();
                    }
                    catch (OperationCanceledException)
                    {
                        operation.PollingTask.OnCanceled();
                        throw;
                    }
                    catch (Exception e)
                    {
                        operation.PollingTask.OnFailed(e);
                    }

                    await UniTask.Delay(operation.Interval, cancellationToken: cancellationToken);
                }
            });
        }
    }
}
