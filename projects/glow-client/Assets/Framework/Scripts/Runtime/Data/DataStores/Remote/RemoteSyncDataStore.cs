using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using WPFramework.Modules.Log;

namespace WPFramework.Data.DataStores
{
    public class RemoteSyncDataStore<T> : IDisposable where T : RemoteSyncData
    {
        Dictionary<string, T> _cachedData;
        readonly Dictionary<string, RemoteSyncTargetData<T>> _pendingSyncData = new Dictionary<string, RemoteSyncTargetData<T>>();
        readonly RemoteSyncProcedure<T> _remoteSyncProcedure;

        readonly CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();

        CancellationTokenSource _syncCancellationTokenSource = new CancellationTokenSource();

        protected bool IsDisposed { get; private set; }

        protected double SyncInterval { get; set; } = 3.0;

        public RemoteSyncDataStore(IRemoteSyncExecutor<T> executor, IRemoteSyncDataErrorHandler dataErrorHandler, IRemoteSyncRetryDelayProtocol retryDelayProtocol)
        {
            _remoteSyncProcedure = new RemoteSyncProcedure<T>(executor, dataErrorHandler, retryDelayProtocol);

            _cachedData = new Dictionary<string, T>();
        }

        void RemoteSyncCancelAndStart()
        {
            // NOTE: 現在実行している同期処理をキャンセルして新しい同期処理を開始する
            _syncCancellationTokenSource?.Cancel();
            _syncCancellationTokenSource?.Dispose();
            _syncCancellationTokenSource = new CancellationTokenSource();

            UniTask.Create(async () =>
                {
                    using var cts =
                        CancellationTokenSource.CreateLinkedTokenSource(
                            _cancellationTokenSource.Token,
                            _syncCancellationTokenSource.Token);
                    await RemoteSyncLoop(cts.Token);
                })
                .Forget(e =>
                {
                     if (e is OperationCanceledException)
                     {
                         ApplicationLog.Log(nameof(RemoteSyncDataStore<T>), "SyncLoop canceled.");
                         return;
                     }

                     // NOTE: エラーを検知しても今のところ何もしない
                     ApplicationLog.LogError(nameof(RemoteSyncDataStore<T>), e.ToString());
            });
        }

        async UniTask RemoteSyncLoop(CancellationToken cancellationToken)
        {
            await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
            {
                // NOTE: 指定秒待ってから同期処理を行う
                await UniTask.Delay(TimeSpan.FromSeconds(SyncInterval), cancellationToken: cancellationToken);

                // NOTE: 変更領域にデータが貯まるまで待つ
                if (_pendingSyncData.Count == 0)
                {
                    continue;
                }

                // NOTE: 変更領域に貯まったデータを取得して同期処理を行う
                //       同期中に変更領域にデータが追加される可能性があるためコピーしておく
                var syncData = _pendingSyncData.Values.ToArray();
                await _remoteSyncProcedure.Sync(cancellationToken, syncData);
                // NOTE: 利用し終わったデータを変更領域から削除
                _pendingSyncData.Clear();
            }
        }

        public void Import(T data)
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            // NOTE: 現在実行している同期処理をキャンセルする
            _syncCancellationTokenSource?.Cancel();
            _syncCancellationTokenSource?.Dispose();
            _syncCancellationTokenSource = null;

            // TODO: Import中にデータがあった場合はバージョニングなどを検討しバージョンによってどのような振る舞いにするかを決める必要がある

            // NOTE: 初回のデータを設定する
            _cachedData[data.Id] = data;
        }

        public void Import(T[] data)
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            // NOTE: 現在実行している同期処理をキャンセルする
            _syncCancellationTokenSource?.Cancel();
            _syncCancellationTokenSource?.Dispose();
            _syncCancellationTokenSource = null;

            // TODO: Import中にデータがあった場合はバージョニングなどを検討しバージョンによってどのような振る舞いにするかを決める必要がある

            // NOTE: 初回のデータを設定する
            _cachedData = data.ToDictionary(x => x.Id, x => x);
        }

        public void SyncAll(T[] data)
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            // NOTE: 追加と更新のデータを処理
            var dataIds = new HashSet<string>(data.Select(d => d.Id));
            foreach (var d in data)
            {
                var remoteSyncType = _cachedData.ContainsKey(d.Id) ? RemoteSyncType.Update : RemoteSyncType.Create;
                SetPendingSyncData(d, remoteSyncType);
            }

            // NOTE: 削除対象になるものを抽出して新しいリストを作成する
            var removedDataKeys = _cachedData.Keys.Where(key => !dataIds.Contains(key)).ToList();
            foreach (var key in removedDataKeys)
            {
                if (_cachedData.TryGetValue(key, out var removedData))
                {
                    SetPendingSyncData(removedData, RemoteSyncType.Remove);
                }
            }

            // NOTE: キャッシュを更新
            _cachedData = data.ToDictionary(x => x.Id, x => x);

            RemoteSyncCancelAndStart();
        }

        public void Upsert(T data)
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            // NOTE: Upsertのデータを処理
            if (AreCachedDataEqual(data))
            {
                return;
            }

            var syncType = _cachedData.ContainsKey(data.Id) ? RemoteSyncType.Update : RemoteSyncType.Create;
            _cachedData[data.Id] = data;

            SetPendingSyncData(data, syncType);

            RemoteSyncCancelAndStart();
        }

        public void Upsert(T[] data)
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            var isDirty = false;
            foreach (var d in data)
            {
                if (AreCachedDataEqual(d))
                {
                    continue;
                }

                // NOTE: Upsertのデータを処理
                var syncType = _cachedData.ContainsKey(d.Id) ? RemoteSyncType.Update : RemoteSyncType.Create;
                _cachedData[d.Id] = d;

                SetPendingSyncData(d, syncType);
                isDirty = true;
            }

            if (!isDirty)
            {
                return;
            }

            RemoteSyncCancelAndStart();
        }

        public void Delete()
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            foreach (var d in _cachedData)
            {
                SetPendingSyncData(d.Value, RemoteSyncType.Remove);
            }

            _cachedData.Clear();

            RemoteSyncCancelAndStart();
        }

        public void Delete(string id)
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            if (!_cachedData.TryGetValue(id, out var removedData))
            {
                return;
            }

            // NOTE: キューに削除対象を追加
            SetPendingSyncData(removedData, RemoteSyncType.Remove);

            // NOTE: キャッシュからデータを削除
            _cachedData.Remove(id);

            RemoteSyncCancelAndStart();
        }

        public void Delete(string[] ids)
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            foreach (var id in ids)
            {
                if (!_cachedData.TryGetValue(id, out var removedData))
                {
                    continue;
                }

                // NOTE: キューに削除対象を追加
                SetPendingSyncData(removedData, RemoteSyncType.Remove);

                // NOTE: キャッシュからデータを削除
                _cachedData.Remove(id);
            }

            RemoteSyncCancelAndStart();
        }

        void SetPendingSyncData(T data, RemoteSyncType syncType)
        {
            _pendingSyncData[data.Id] = new RemoteSyncTargetData<T>((T)data.Clone(), syncType);
        }

        bool AreCachedDataEqual(T data)
        {
            if (_cachedData.TryGetValue(data.Id, out var cachedData) == false)
            {
                return false;
            }

            // NOTE: データが同じかどうかを判定
            var ret = data.Equals(cachedData);
            if (!ret)
            {
                return false;
            }

            // NOTE: 同期待ちに登録されている場合は再度送信する必要があるためfalseを返す
            if (_pendingSyncData.TryGetValue(data.Id, out var pendingData))
            {
                ret = false;
            }

            ApplicationLog.Log(nameof(RemoteSyncDataStore<T>), $"Data is equal. Id: {data.Id}");
            return ret;
        }

        public T[] GetAll()
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            return _cachedData.Values.ToArray();
        }

        public T FindById(string id)
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            return _cachedData.GetValueOrDefault(id);
        }

        public async UniTask WaitForCompletion(CancellationToken cancellationToken, TimeSpan timeout)
        {
            if (IsDisposed)
            {
                throw new ObjectDisposedException(nameof(RemoteSyncDataStore<T>));
            }

            using var cts =
                CancellationTokenSource.CreateLinkedTokenSource(
                    _cancellationTokenSource.Token,
                    _syncCancellationTokenSource.Token,
                    cancellationToken);

            // NOTE: 同期処理が完了するまで待つ
            await _remoteSyncProcedure.WaitForCompletion(cts.Token, timeout);
        }

        public void Dispose()
        {
            if (IsDisposed)
            {
                return;
            }

            IsDisposed = true;

            _cancellationTokenSource.Cancel();
            _cancellationTokenSource.Dispose();
            _pendingSyncData.Clear();

            ApplicationLog.Log(nameof(RemoteSyncDataStore<T>), "Disposed.");
        }
    }
}
