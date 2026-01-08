using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine.Scripting;
using WonderPlanet.InAppAdvertising;
using WonderPlanet.InAppAdvertising.Exceptions;
using WPFramework.Modules.Log;

namespace WPFramework.Domain.Modules
{
    public sealed class AdvertisingBackgroundLoader : IDisposable
    {
        IInAppAdvertisingAgent InAppAdvertisingAgent { get; }

        readonly Dictionary<string, UniTask> _loadAdTasks = new Dictionary<string, UniTask>();

        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        bool _isDisposed;

        [Preserve]
        public AdvertisingBackgroundLoader(IInAppAdvertisingAgent inAppAdvertisingAgent)
        {
            InAppAdvertisingAgent = inAppAdvertisingAgent;
        }

        static string GenerateHandlingId()
        {
            return $"advertising_backgroundloader_{Guid.NewGuid()}";
        }

        public void LoadRewardedAd(CancellationToken cancellationToken, string adUnit, IAdvertisingBackgroundLoadEventHandler eventHandler)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }

            var adHandlingId = GenerateHandlingId();
            LoadRewardedAd(cancellationToken, adUnit, adHandlingId, eventHandler);
        }

        void LoadRewardedAd(CancellationToken cancellationToken, string adUnit, string adHandlingId, IAdvertisingBackgroundLoadEventHandler eventHandler)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }

            if (_loadAdTasks.ContainsKey(adHandlingId))
            {
                ApplicationLog.Log(nameof(AdvertisingBackgroundLoader), $"Already loading ad: {adUnit} {adHandlingId} ({_loadAdTasks.Count})");
                return;
            }

            if (InAppAdvertisingAgent.IsLoadedAd(adHandlingId))
            {
                ApplicationLog.Log(nameof(AdvertisingBackgroundLoader), $"Already loaded ad: {adUnit} {adHandlingId}");
                return;
            }

            ApplicationLog.Log(nameof(AdvertisingBackgroundLoader), $"Start loading ad: {adUnit} {adHandlingId}");
            var cts =
                CancellationTokenSource.CreateLinkedTokenSource(_cancellationTokenSource.Token, cancellationToken);
            var task = UniTask.Create(async () =>
            {
                try
                {
                    // NOTE: 同一フレームで完了する可能性があるためYieldを挟む
                    await UniTask.Yield(cts.Token);

                    var adHandlingToken = await InAppAdvertisingAgent.LoadRewardedAd(cancellationToken, adUnit, adHandlingId);

                    ApplicationLog.Log(nameof(AdvertisingBackgroundLoader), $"Complete loading ad: {adUnit} {adHandlingId}");

                    eventHandler?.OnAdCompletedToLoad(adHandlingToken);
                }
                catch (OperationCanceledException)
                {
                    eventHandler?.OnAdCanceledToLoad(adHandlingId);

                    // NOTE: 破棄した後にキャンセルされる可能性があるため、破棄済みの場合は何もしない
                    if (InAppAdvertisingAgent.IsDisposed)
                    {
                        return;
                    }

                    InAppAdvertisingAgent.DestroyAd(adHandlingId);
                }
                catch (InAppAdvertisingLoadException e)
                {
                    eventHandler?.OnAdFailedToLoad(e, adHandlingId);

                    // NOTE: 破棄した後に実行される可能性があるため、破棄済みの場合は何もしない
                    if (InAppAdvertisingAgent.IsDisposed)
                    {
                        return;
                    }

                    InAppAdvertisingAgent.DestroyAd(adHandlingId);
                }
                finally
                {
                    cts.Dispose();

                    _loadAdTasks.Remove(adHandlingId);
                    ApplicationLog.Log(nameof(AdvertisingBackgroundLoader),
                        $"Remove loading ad: {adUnit} {adHandlingId} ({_loadAdTasks.Count})");
                }
            });
            _loadAdTasks.Add(adHandlingId, task);
            task.Forget();
        }

        public bool IsRequestingAd(string uniqueId)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }

            return _loadAdTasks.ContainsKey(uniqueId);
        }

        public IEnumerable<string> GetRequestingAds()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }

            return _loadAdTasks.Keys;
        }

        public void CancelAll()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }

            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = new CancellationTokenSource();
        }

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
        }
    }
}
