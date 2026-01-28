#if UNITY_IOS
using System.Collections.Generic;
using Adfurikun;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.Advertising.AppIdResolver;
using UnityEngine;
using ApplicationLog = WPFramework.Modules.Log.ApplicationLog;

namespace GLOW.Core.Modules.Advertising.AdfurikunAgent
{
    public class AdfurikunRewardAdEventHandleriOS
    {
        GlowRewardAppId _appId;
        float _timeOut;
        public float TimeOut => _timeOut;

        UniTaskCompletionSource _showRewardAdCompletionSource;
        AdfurikunPlayRewardResultType _playRewardResultType = AdfurikunPlayRewardResultType.None;

        public AdfurikunRewardAdEventHandleriOS(float timeOut, GlowRewardAppId appId)
        {
            _timeOut = timeOut;
            _appId = appId;
            AdfMovieRewardIOS.callbacks.OnPrepareSuccess += IOSPrepareSuccess;
            AdfMovieRewardIOS.callbacks.OnPrepareFailure += IOSPrepareFailure;

            AdfMovieRewardIOS.callbacks.OnPlayStart += IOSPlayStart;
            AdfMovieRewardIOS.callbacks.OnPlayFinish += IOSPlayFinish;
            AdfMovieRewardIOS.callbacks.OnPlayFailed += IOSPlayFailed;
            AdfMovieRewardIOS.callbacks.OnCloseAd += IOSCloseAd;//このタイミングで報酬付与するのが望ましい
        }

        public void Dispose()
        {
            AdfMovieRewardIOS.callbacks.OnPrepareSuccess -= IOSPrepareSuccess;
            AdfMovieRewardIOS.callbacks.OnPrepareFailure -= IOSPrepareFailure;

            AdfMovieRewardIOS.callbacks.OnPlayStart -= IOSPlayStart;
            AdfMovieRewardIOS.callbacks.OnPlayFinish -= IOSPlayFinish;
            AdfMovieRewardIOS.callbacks.OnPlayFailed -= IOSPlayFailed;
            AdfMovieRewardIOS.callbacks.OnCloseAd -= IOSCloseAd;
        }


        void IOSPrepareSuccess(string appId, bool isManualMode)
        {
            ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandleriOS), "Reward - IOSPrepareSuccess");
        }

        void IOSPrepareFailure(string appId, int errorCode, string errorMessage, string adNetworkErrorInfo)
        {
            var message =
                "Reward - PrepareFailureHandler\n" +
                $"{errorMessage}\n"+
                $"{adNetworkErrorInfo}";
            ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandleriOS),message);
        }

        void IOSPlayStart(string appId, string adNetworkKey)
        {
            ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandleriOS), "Reward - IOSPlayStart");
            _playRewardResultType = AdfurikunPlayRewardResultType.NotFinished;
        }

        void IOSPlayFinish(string appId)
        {
            ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandleriOS), "Reward - IOSPlayFinish");
            _playRewardResultType = AdfurikunPlayRewardResultType.Finish;
        }

        void IOSPlayFailed(string appId, string adNetworkErrorInfo)
        {
            ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandleriOS), "Reward - IOSPlayFailed");
            _playRewardResultType = AdfurikunPlayRewardResultType.Failed;
        }

        void IOSCloseAd(string appId, bool isRewarded)
        {
            ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandleriOS), "Reward - IOSCloseAd");
            _showRewardAdCompletionSource.TrySetResult();
        }

        public bool IsPreparedMovieReward(string appId)
        {
            return AdfMovieRewardIOS.isPrepared(appId);
        }

        public async UniTask<AdfurikunPlayRewardResultType> ShowRewardAd(
            IAARewardFeatureType iaaRewardFeatureType,
            UniTaskCompletionSource completionSource)
        {
            _showRewardAdCompletionSource = completionSource;
            var appId = _appId.GetAppId();

            if (Application.platform != RuntimePlatform.IPhonePlayer)
            {
                return AdfurikunPlayRewardResultType.None;
            }

            if (!IsPreparedMovieReward(appId))
            {
                ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandleriOS), "not loaded yet");
                return AdfurikunPlayRewardResultType.NotLoaded;
            }

            var convertCustomParam = ConvertDictionaryToString(
                AdfurikunCustomParamConstExtensions.CreateCustomParam(iaaRewardFeatureType));

            AdfMovieRewardIOS.play(appId, convertCustomParam);

            // 広告終了まで待機する
            await _showRewardAdCompletionSource.Task;

            var result = PostShowRewardAd();
            return result;
        }
        string ConvertDictionaryToString(Dictionary<string, string> dic)
        {
            var result = "";
            foreach (var elem in dic)
            {
                result += elem.Key + ":" + elem.Value + ",";
            }
            return result;
        }

        AdfurikunPlayRewardResultType PostShowRewardAd()
        {
            // 広告再生後の副作用はこのメソッドに記述する
            var result = _playRewardResultType;
            _playRewardResultType = AdfurikunPlayRewardResultType.None;

            _showRewardAdCompletionSource = null;

            return result;
        }

    }
}
#endif
