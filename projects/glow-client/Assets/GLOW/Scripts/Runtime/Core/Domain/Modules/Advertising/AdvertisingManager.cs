using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Exceptions;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Modules.Advertising.AppIdResolver;
using UnityEngine;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Core.Modules.Advertising
{
    public sealed class AdvertisingManager :
        IGlowAdvertisingBackgroundLoadEventHandler,
        IAdvertisingManager,
        IAdvertisingPlayer,
        IDisposable
    {
        enum InitializeState
        {
            None,
            Initializing,
            Initialized,
            Failed,
            EditorInitialized,
        }

        [Inject] IGlowInAppAdvertisingAgent InAppAdvertisingAgent { get; }
        [Inject] IGlowRewardAppIdResolver GlowRewardAppIdResolver { get; }

        CancellationTokenSource _cancellationTokenSource = new();
        IGlowAdvertisingBackgroundLoader _advertisingBackgroundLoader;


        bool IAdvertisingManager.IsInitialized =>
            _initializeState == InitializeState.Initialized && InAppAdvertisingAgent.IsInitialized;

        InitializeState _initializeState = InitializeState.None;
        bool _isDisposed;

        void IAdvertisingManager.Initialize()
        {
            Initialize();
        }

        void Initialize()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingManager));
            }

            if (Application.isEditor)
            {
                // アドフリくんSDKは実機でのみ動作するので、UnityEditor上では何もしない
                _initializeState = InitializeState.EditorInitialized;
                return;
            }

            // ネットワークチェック
            // ネットワークが無いときにInAppAdvertisingAgent.Initializeを初期化すると広告配信状況が無い扱いで初期化される
            if (Application.internetReachability == NetworkReachability.NotReachable)
            {
                ApplicationLog.Log(nameof(AdvertisingManager), " Initialize failed: not network reachable.");
                return;
            }

            _initializeState = InitializeState.Initializing;

            try
            {
                _cancellationTokenSource?.Cancel();
                _cancellationTokenSource?.Dispose();
                _cancellationTokenSource = new CancellationTokenSource();

                _advertisingBackgroundLoader?.Dispose();
                _advertisingBackgroundLoader = new GlowAdvertisingBackgroundLoader(InAppAdvertisingAgent);

                if (!InAppAdvertisingAgent.IsInitialized)
                {
                    InitializeAgent();
                }

                _initializeState = InitializeState.Initialized;

                ApplicationLog.Log(nameof(AdvertisingManager), "Initialize Complete");
            }
            catch (Exception)
            {
                _initializeState = InitializeState.Failed;
                throw;
            }
        }

        void InitializeAgent()
        {
            // GDPRなど初期化必要ならここで行う

            var rewardAppId = GlowRewardAppIdResolver.Resolve();
            InAppAdvertisingAgent.Initialize(rewardAppId);
        }

        void IAdvertisingManager.LoadAndCacheRewardedAd(CancellationToken cancellationToken)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingManager));
            }

            // NOTE: この処理自体は非同期で実行されるため読み込みオーダーも非同期で実行する
            LoadAndCacheRewardAd(cancellationToken).Forget();
        }

        async UniTask LoadAndCacheRewardAd(CancellationToken ct)
        {
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(
                _cancellationTokenSource.Token,
                ct);

            // ネットワークチェック
            if (Application.internetReachability == NetworkReachability.NotReachable)
            {
                ApplicationLog.Log(nameof(AdvertisingManager), " LoadAndCacheRewardAd: not network reachable.");
                return;
            }

            if (_initializeState is InitializeState.None)
            {
                ApplicationLog.Log(nameof(AdvertisingManager), " LoadAndCacheRewardAd: not initialized, initialize now.");
                Initialize();
            }

            // NOTE: 初期化が終わるまで待機する
            await UniTask.WaitUntil(
                () => _initializeState
                    is InitializeState.Initialized
                    or InitializeState.Failed
                    or InitializeState.EditorInitialized,
                cancellationToken: cts.Token);


            // NOTE: 初期化に失敗 or Editor上では何もしない
            if (_initializeState is InitializeState.Failed or InitializeState.EditorInitialized)
            {
                return;
            }

            // NOTE: 広告のキャッシュを作成する
            LoadAndCacheRewardedAd(cts.Token);

        }

        void IAdvertisingManager.DestroyAdAll()
        {
            if (_isDisposed)
            {
                return;
            }

            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = new CancellationTokenSource();

            _advertisingBackgroundLoader.CancelAll();

            ApplicationLog.Log(nameof(AdvertisingManager), "DestroyAdAll");
        }

        // 広告スキップのときは呼ばないように注意
        async UniTask<GlowAdPlayRewardResultData> IAdvertisingPlayer.ShowAdAsync(
            IAARewardFeatureType iaaRewardFeatureType,
            CancellationToken cancellationToken)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(AdvertisingManager));
            }

            // ネットワークチェック
            if (Application.internetReachability == NetworkReachability.NotReachable)
            {
                ApplicationLog.Log(nameof(AdvertisingManager), " ShowAdAsync: not network reachable.");
                return new GlowAdPlayRewardResultData(AdfurikunPlayRewardResultType.NetworkNotReachable);
            }

            if (_initializeState is InitializeState.None)
            {
                ApplicationLog.Log(nameof(AdvertisingManager), " ShowAdAsync: not initialized, initialize now.");
                Initialize();
            }

            // NOTE: 初期化が終わるまで待機する
            await UniTask.WaitUntil(() =>
                    _initializeState
                        is InitializeState.Initialized
                        or InitializeState.Failed
                        or InitializeState.EditorInitialized,
                cancellationToken: cancellationToken);

            // NOTE: 初期化に失敗 or Editor上では何もしない
            if (_initializeState is InitializeState.Failed or InitializeState.EditorInitialized)
            {
                return GlowAdPlayRewardResultData.Empty;
            }

            try
            {
                using var cts = CancellationTokenSource.CreateLinkedTokenSource(
                    _cancellationTokenSource.Token,
                    cancellationToken);

                // NOTE: 未ロード場合はLoad処理する
                if (!InAppAdvertisingAgent.IsLoadedAd())
                {
                    ApplicationLog.Log(nameof(AdvertisingManager), "No loaded ad, reload");

                    LoadAndCacheRewardedAd(cts.Token);
                    await UniTask.WaitUntil(() => InAppAdvertisingAgent.IsLoadedAd(), cancellationToken: cts.Token);
                }

                // NOTE: 広告を表示する
                var result = await InAppAdvertisingAgent.ShowAdAsync(iaaRewardFeatureType, cancellationToken: cts.Token);


                //IAAPlayRewardExceptionを投げる(報酬付与させない)
                // typeがStartのときは、ユーザーが視聴キャンセルした(キャンセル時HandlerのFinishもFailedも通らない)としてエラーにはしない
                if (result.Type is AdfurikunPlayRewardResultType.None or AdfurikunPlayRewardResultType.Failed)
                {
                    throw new IAAPlayRewardException($"AdfurikunPlayRewardResultType is not Finish...{result.Type}");
                }

                return result;
            }
            finally
            {
                // NOTE: 視聴し終わったらバックグラウンドで再度読み込む
                //       ここのローディングは外から渡されてきたCancellationTokenでキャンセルされないようにする
                //       AdvertisingBackgroundLoaderにキャンセルなどを管理してもらう
                LoadAndCacheRewardedAd(default);
            }
        }

        void LoadAndCacheRewardedAd(CancellationToken cancellationToken)
        {
            if (_isDisposed)
            {
                return;
            }

            if (_advertisingBackgroundLoader.IsRequestingAd())
            {
                return;
            }

            var rewardAppId = GlowRewardAppIdResolver.Resolve();

            using var cts = CancellationTokenSource.CreateLinkedTokenSource(
                _cancellationTokenSource.Token,
                cancellationToken);
            _advertisingBackgroundLoader.LoadRewardAd(
                cancellationToken: cts.Token,
                rewardAppId: rewardAppId,
                eventHandler: this);
        }

        void IGlowAdvertisingBackgroundLoadEventHandler.OnAdCompletedToLoad()
        {
            ApplicationLog.Log(nameof(AdvertisingManager), "Loaded ad");
        }

        void IGlowAdvertisingBackgroundLoadEventHandler.OnAdFailedToLoad(Exception exception)
        {
            // NOTE: adHandlingIdはAdvertisingBackgroundLoaderが払い出したIDになる
            ApplicationLog.LogError(
                nameof(AdvertisingManager),
                $"Failed loading ad: {exception.Message}({exception.GetType()})"
                );
        }

        void IGlowAdvertisingBackgroundLoadEventHandler.OnAdCanceledToLoad()
        {
            // NOTE: adHandlingIdはAdvertisingBackgroundLoaderが払い出したIDになる
            ApplicationLog.Log(nameof(AdvertisingManager), $"Canceled loading ad");
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

            _advertisingBackgroundLoader?.Dispose();

            ApplicationLog.Log(nameof(AdvertisingManager), "Dispose");
        }
    }
}
