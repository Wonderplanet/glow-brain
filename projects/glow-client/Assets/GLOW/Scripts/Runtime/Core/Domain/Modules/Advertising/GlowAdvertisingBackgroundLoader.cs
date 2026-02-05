using System;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Modules.Advertising.AppIdResolver;
using UnityEngine.Scripting;
using WonderPlanet.InAppAdvertising.Exceptions;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;

namespace GLOW.Core.Modules.Advertising
{
    public sealed class GlowAdvertisingBackgroundLoader : IGlowAdvertisingBackgroundLoader
    {
        IGlowInAppAdvertisingLoader GlowInAppAdvertisingLoader { get; }

        (string, UniTask) _loadAdTask;

        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        bool _isDisposed;

        [Preserve]
        public GlowAdvertisingBackgroundLoader(IGlowInAppAdvertisingLoader glowInAppAdvertisingLoader)
        {
            GlowInAppAdvertisingLoader = glowInAppAdvertisingLoader;
        }

        // seed: handlingAdId..._cachedDataでFirstOrDefaultで取得するために用意されている
        // AdvertisingManageでneedsLoadRequestCountだけ複数Load処理回しているからhandingIdが必要。
        // アドフリくんでは1つしかLoadできないので、ここのLoad管理にのみ利用する
        static string GenerateHandlingId()
        {
            return ZString.Format("advertising_backgroundloader_{0}", Guid.NewGuid());
        }

        void IGlowAdvertisingBackgroundLoader.LoadRewardAd(
            CancellationToken cancellationToken,
            GlowRewardAppId rewardAppId,
            IGlowAdvertisingBackgroundLoadEventHandler eventHandler)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }

            LoadRewardedAd(cancellationToken, rewardAppId, eventHandler);
        }

        void LoadRewardedAd(
            CancellationToken cancellationToken,
            GlowRewardAppId rewardAppId,
            IGlowAdvertisingBackgroundLoadEventHandler eventHandler)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }
            if (GlowInAppAdvertisingLoader.IsLoadedAd())
            {
                ApplicationLog.Log(nameof(AdvertisingBackgroundLoader), "Already loaded ad");
                return;
            }

            ApplicationLog.Log(nameof(AdvertisingBackgroundLoader), "Start loading ad");

            var task = CreateLoadTask(cancellationToken, rewardAppId, eventHandler);
            _loadAdTask = (GenerateHandlingId(), task);
            _loadAdTask.Item2.Forget();
        }

        UniTask CreateLoadTask(
            CancellationToken cancellationToken,
            GlowRewardAppId rewardAppId,
            IGlowAdvertisingBackgroundLoadEventHandler eventHandler)
        {
            var cts =
                CancellationTokenSource.CreateLinkedTokenSource(_cancellationTokenSource.Token, cancellationToken);

            return UniTask.Create(async () =>
            {
                try
                {
                    // NOTE: 同一フレームで完了する可能性があるためYieldを挟む
                    await UniTask.Yield(cts.Token);

                    await GlowInAppAdvertisingLoader.LoadRewardedAd(cancellationToken, rewardAppId);

                    ApplicationLog.Log(nameof(AdvertisingBackgroundLoader), "Complete loading ad");

                    eventHandler?.OnAdCompletedToLoad();
                }
                catch (OperationCanceledException)
                {
                    eventHandler?.OnAdCanceledToLoad();
                    ApplicationLog.Log(
                        nameof(AdvertisingBackgroundLoader),
                        $"OperationCanceledException ad: ({_loadAdTask.Item1})");
                }
                catch (InAppAdvertisingLoadException e)
                {
                    eventHandler?.OnAdFailedToLoad(e);
                    ApplicationLog.LogWarning(
                        nameof(AdvertisingBackgroundLoader),
                        $"InAppAdvertisingLoadException ad: ({_loadAdTask.Item1})");
                }
                finally
                {
                    cts.Dispose();

                    ApplicationLog.Log(nameof(AdvertisingBackgroundLoader), $"Remove loading ad: ({_loadAdTask.Item1})");
                    _loadAdTask = (String.Empty, default);
                }
            });
        }

        bool IGlowAdvertisingBackgroundLoader.IsRequestingAd()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }

            return !string.IsNullOrEmpty(_loadAdTask.Item1);
        }

        string IGlowAdvertisingBackgroundLoader.GetRequestingAd()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }

            return _loadAdTask.Item1;
        }

        void IGlowAdvertisingBackgroundLoader.CancelAll()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingBackgroundLoader));
            }

            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = new CancellationTokenSource();
        }

        void IDisposable.Dispose()
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
