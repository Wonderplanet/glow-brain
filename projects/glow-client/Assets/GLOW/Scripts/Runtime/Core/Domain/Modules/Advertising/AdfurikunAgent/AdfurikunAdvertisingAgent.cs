using System;
using System.Threading;
using Adfurikun;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.Advertising.AppIdResolver;
using UnityEngine;
using WonderPlanet.InAppAdvertising.Exceptions;
using ApplicationLog = WPFramework.Modules.Log.ApplicationLog;


namespace GLOW.Core.Modules.Advertising.AdfurikunAgent
{
    public class AdfurikunAdvertisingAgent : IGlowInAppAdvertisingAgent
    {
        const float DefaultTimeOut = 30.0f;
        const string AdfurikunSdkVersion = "4.0.0";

        public bool IsInitialized { get; private set; }
        public bool IsDisposed { get; private set; }

#if UNITY_IOS
        AdfurikunRewardAdEventHandleriOS _iOSRewardHandler;
#elif UNITY_ANDROID
        AdfurikunRewardAdEventHandlerAndroid _androidRewardHandler;
#endif

        GlowRewardAppId _rewardAppId;

        readonly CancellationTokenSource _cancellationTokenSource = new();

        void IGlowInAppAdvertisingAgent.Initialize(GlowRewardAppId rewardAppId)
        {
            _rewardAppId = rewardAppId;
            ThrowIfDisposed();
            if(IsInitialized)
            {
                ApplicationLog.LogWarning(nameof(AdfurikunAdvertisingAgent), "Already initialized");
                return;
            }

            InitializeAdfurikunSdk(rewardAppId);
            IsInitialized = true;
        }

        //SDK初期化処理
        void InitializeAdfurikunSdk(GlowRewardAppId rewardAppId)
        {
            if (Application.isEditor)
            {
                return;
            }

            SetOptions();

            var appId = rewardAppId.GetAppId();
            if (!AdfurikunAdvertisingAgentEvaluator.IsValidAppID(appId))
            {
                throw new InAppAdvertisingInitializeException("appId is Invalid");
            }

            var unityVersion = Application.unityVersion;

#if UNITY_IOS
            AdfMovieRewardIOS.initialize(appId, AdfurikunSdkVersion, unityVersion);
            _iOSRewardHandler = new AdfurikunRewardAdEventHandleriOS(DefaultTimeOut, rewardAppId);
#elif UNITY_ANDROID
            AdfAndroidBridge.AdfurikunSdkCall("setPlatformInfo", "unity", AdfurikunSdkVersion);
            AdfAndroidBridge.AdfurikunSdkCall("setPlatformEngineInfo", "unity", unityVersion);
            var activity = AdfAndroidBridge.GetCurrentActivity();
            if (activity != null && _androidRewardHandler == null)
            {
                var reward = new AdfMovieReward(activity, appId);
                _androidRewardHandler = new AdfurikunRewardAdEventHandlerAndroid(reward, DefaultTimeOut);
            }
#endif
        }

        void SetOptions()
        {
#if GLOW_DEBUG
            // テストモードを設定（true: ON、false: OFF）
            AdfurikunOptions.SetTestMode(true);
            // Debug Logを設定
            AdfurikunOptions.SetDebugMode(true);
            // 広告の音を出力設定をします。デフォルト設定ではリワード、インタースティシャルは音有り、ネイティブは音無しです。
            // AdfurikunOptions.SetMovieSoundState(true);
#else
            AdfurikunOptions.SetTestMode(false);
            AdfurikunOptions.SetDebugMode(false);
            // AdfurikunOptions.SetMovieSoundState(true);
#endif
        }

        async UniTask IGlowInAppAdvertisingLoader.LoadRewardedAd(
            CancellationToken cancellationToken,
            GlowRewardAppId rewardAppId)
        {
            if (Application.isEditor)
            {
                // 実機でないとアドフリくんSDKは利用不能なので、即時返す
                return;
            }

            var appId = rewardAppId.GetAppId();

            if (!AdfurikunAdvertisingAgentEvaluator.IsValidAppID(appId))
            {
                throw new InAppAdvertisingLoadException(-1, "Invalid App ID");
            }



            using var cts =
                CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            try
            {
#if UNITY_IOS
                if (_iOSRewardHandler.TimeOut > 0.0)
                {
                    AdfMovieRewardIOS.loadWithTimeout(appId, _iOSRewardHandler.TimeOut);
                }
                else
                {
                    AdfMovieRewardIOS.load(appId);
                }

                await UniTask.WaitUntil(() => _iOSRewardHandler.IsPreparedMovieReward(appId), cancellationToken: cts.Token);
#elif UNITY_ANDROID
                if (_androidRewardHandler.Reward != null && appId.Equals(_androidRewardHandler.Reward.appId))
                {
                    if (_androidRewardHandler.TimeOut > 0)
                    {
                        _androidRewardHandler.Reward.LoadWithTimeout(_androidRewardHandler.TimeOut);
                    }
                    else
                    {
                        _androidRewardHandler.Reward.Load();
                    }
                }

                await UniTask.WaitUntil(() => _androidRewardHandler.IsPreparedMovieReward(appId), cancellationToken: cts.Token);
#endif

                // NOTE: ネイティブメソッドを実行するとメインスレッドから外れるので、メインスレッドへ戻す
                await UniTask.SwitchToMainThread(cancellationToken: cts.Token);

                cts.Token.ThrowIfCancellationRequested();
            }
            finally
            {
                // NOTE: 必ずメインスレッドへ戻す
                await UniTask.SwitchToMainThread(cancellationToken: cts.Token);
            }
        }

        async UniTask<GlowAdPlayRewardResultData> IGlowInAppAdvertisingAgent.ShowAdAsync(
            IAARewardFeatureType iaaRewardFeatureType,
            CancellationToken cancellationToken)
        {
            ThrowIfDisposed();
            ThrowIfNotInitialized();

            using var cts =
                CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            try
            {
                ApplicationLog.Log(nameof(AdfurikunAdvertisingAgent), "ShowAdAsync: start reward ad.");

                var result = await PlayAdfurikunReward(iaaRewardFeatureType, cts.Token);
                // NOTE: ネイティブメソッドを実行するとメインスレッドから外れるので、メインスレッドへ戻す
                await UniTask.SwitchToMainThread(cancellationToken: cts.Token);

                return result;
            }
            catch (OperationCanceledException)
            {
                ApplicationLog.LogWarning(nameof(AdfurikunAdvertisingAgent), "ShowAdAsync: canceled");
                throw;
            }
            finally
            {
                // NOTE: 必ずメインスレッドへ戻す
                await UniTask.SwitchToMainThread(cancellationToken: cts.Token);
            }
        }

        async UniTask<GlowAdPlayRewardResultData> PlayAdfurikunReward(
            IAARewardFeatureType iaaRewardFeatureType,
            CancellationToken cancellationToken)
        {
            using var cts =
                CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, _cancellationTokenSource.Token);

            var completionSource = new UniTaskCompletionSource();
            // NOTE: キャンセルを行えるようにキャンセル動作に処理を登録する
            await using var registration = cts.Token.Register(() =>
            {
                ApplicationLog.Log(nameof(AdfurikunAdvertisingAgent), "ShowAdAsync: canceled");
                completionSource.TrySetCanceled();
            });

            // NOTE: リワード広告を表示
            //       エディタの場合は動画再生できないので、各種コールバックが届かない
            var resultData = GlowAdPlayRewardResultData.Empty;
#if UNITY_IOS
            var type = await _iOSRewardHandler.ShowRewardAd(iaaRewardFeatureType, completionSource);
            resultData = new GlowAdPlayRewardResultData(type);
#elif UNITY_ANDROID
            var appId = _rewardAppId.GetAppId();
            var type = await _androidRewardHandler.ShowRewardAd(completionSource, appId, iaaRewardFeatureType);
            resultData = new GlowAdPlayRewardResultData(type);
#endif
            // NOTE: 報酬検証データを返却する
            return resultData;
        }


        bool IGlowInAppAdvertisingLoader.IsLoadedAd()
        {
            ThrowIfDisposed();
            ThrowIfNotInitialized();
            if (Application.isEditor)
            {
                return false;
            }

            var appId = _rewardAppId.GetAppId();
#if UNITY_IOS
            return _iOSRewardHandler.IsPreparedMovieReward(appId);
#elif UNITY_ANDROID
            return _androidRewardHandler.IsPreparedMovieReward(appId);
#else
            return false;
#endif
        }

        void ThrowIfDisposed()
        {
            if (!IsDisposed)
            {
                return;
            }

            throw new ObjectDisposedException(nameof(AdfurikunAdvertisingAgent));
        }

        void ThrowIfNotInitialized()
        {
            if (IsInitialized)
            {
                return;
            }

            throw new InAppAdvertisingInitializeException("AdfurikunAdvertisingAgent is not initialized");
        }

        void IDisposable.Dispose()
        {
            if (IsDisposed)
            {
                ApplicationLog.LogWarning($"{nameof(AdfurikunAdvertisingAgent)}", "Already disposed");
                return;
            }

            _cancellationTokenSource.Cancel();
            _cancellationTokenSource.Dispose();

            DisposeResource();
            IsDisposed = true;
        }

        void DisposeResource()
        {
            if (Application.isEditor)
            {
                return;
            }
#if UNITY_IOS
            if (_iOSRewardHandler != null)
            {
                _iOSRewardHandler.Dispose();
                _iOSRewardHandler = null;
            }
            AdfMovieRewardIOS.dispose();
#elif UNITY_ANDROID
            if (_androidRewardHandler.Reward != null)
            {
                _androidRewardHandler.Reward.OnDestroy();
                _androidRewardHandler = null;
            }
#endif
        }
    }
}
