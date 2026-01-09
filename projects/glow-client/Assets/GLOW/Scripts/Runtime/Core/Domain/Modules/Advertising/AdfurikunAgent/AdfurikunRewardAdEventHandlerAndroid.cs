#if UNITY_ANDROID
using Adfurikun;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using UnityEngine;
using WonderPlanet.InAppAdvertising.Exceptions;
using ApplicationLog = WPFramework.Modules.Log.ApplicationLog;

public class AdfurikunRewardAdEventHandlerAndroid
{
    readonly AdfMovieReward _reward;// appIdが内包されている
    public AdfMovieReward Reward => _reward;
    float _timeOut;
    public float TimeOut => _timeOut;

    UniTaskCompletionSource _showRewardAdCompletionSource;
    AdfurikunPlayRewardResultType _playRewardResultType = AdfurikunPlayRewardResultType.None;


    public AdfurikunRewardAdEventHandlerAndroid(AdfMovieReward reward, float timeOut)
    {
        _timeOut = timeOut;
        _reward = reward;
        _reward.Events.OnPrepareSuccess += PrepareSuccessHandler;
        _reward.Events.OnPrepareFailure += PrepareFailureHandler;
        _reward.Events.OnPlayStart += PlayStartHandler;
        _reward.Events.OnPlayFailed += PlayFailedHandler;
        _reward.Events.OnPlayFinish += PlayFinishHandler;
        _reward.Events.OnCloseAd += CloseAdHandler;
    }

    void PrepareSuccessHandler(string appId, bool isManualMode)
    {
        ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandlerAndroid), "Reward - PrepareSuccessHandler");
    }

    void PrepareFailureHandler(string appId, AdfMovieError error)
    {
        var errorMessage =
            $"{nameof(AdfurikunRewardAdEventHandlerAndroid)} / Reward - PrepareFailureHandler\n" +
            $"{error.errorCode}\n"+
            $"{error.errorMessage}";
        ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandlerAndroid),errorMessage);
    }

    void PlayStartHandler(AdfMovieData data)
    {
        ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandlerAndroid), "Reward - PlayStartHandler");
        _playRewardResultType = AdfurikunPlayRewardResultType.NotFinished;
    }

    void PlayFinishHandler(AdfMovieData data)
    {
        ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandlerAndroid), "Reward - PlayFinishHandler");
        _playRewardResultType = AdfurikunPlayRewardResultType.Finish;
    }

    void PlayFailedHandler(AdfMovieData data, AdfMovieError error)
    {
        ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandlerAndroid), "PlayFailedHandler");
        _playRewardResultType = AdfurikunPlayRewardResultType.Failed;
    }

    void CloseAdHandler(AdfMovieData data, bool isRewarded)
    {
        ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandlerAndroid), "CloseAdHandler");
        _showRewardAdCompletionSource.TrySetResult();
    }

    public bool IsPreparedMovieReward(string appId)
    {
        if (_reward != null && appId.Equals(_reward.appId))
        {
            return _reward.IsPrepared();
        }

        return false;
    }

    public async UniTask<AdfurikunPlayRewardResultType> ShowRewardAd(
        UniTaskCompletionSource completionSource,
        string appId,
        IAARewardFeatureType iaaRewardFeatureType
        )
    {
        _showRewardAdCompletionSource = completionSource;

        if (Application.platform != RuntimePlatform.Android)
        {
            return AdfurikunPlayRewardResultType.None;
        }

        if (!IsPreparedMovieReward(appId))
        {
            ApplicationLog.Log(nameof(AdfurikunRewardAdEventHandlerAndroid), "not loaded yet");
            return AdfurikunPlayRewardResultType.NotLoaded;
        }

        _reward.Play(AdfurikunCustomParamConstExtensions.CreateCustomParam(iaaRewardFeatureType));

        // 広告終了まで待機する
        await _showRewardAdCompletionSource.Task;

        var result = PostShowRewardAd();
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
#endif
