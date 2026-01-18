using System;
using Cysharp.Threading.Tasks;
using WonderPlanet.SceneManagement;
using WonderPlanet.UniTaskSupporter;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Network;
using GLOW.Scenes.Splash.Domain.UseCase;
using GLOW.Scenes.Splash.Presentation.Views;
using Zenject;

#if GLOW_DEBUG
using GLOW.Debugs.Environment.Domain;
using GLOW.Debugs.Environment.Domain.UseCases;
#endif // GLOW_DEBUG

namespace GLOW.Scenes.Splash.Presentation.Presenters
{
    public class SplashPresenter : IISplashViewDelegate
    {
        [Inject] SetUpUserPropertyUseCase SetUpUserPropertyUseCase { get; }
        [Inject] BuildEnvironmentUseCase BuildEnvironmentUseCase { get; }
        [Inject] SplashViewController ViewController { get; }
        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] IApiContextHostBuilder ApiContextHostBuilder { get; }

#if GLOW_DEBUG
        [Inject] IDebugEnvironmentSelectorInvoker DebugEnvironmentSelectorInvoker { get; }
        [Inject] DebugBuildEnvironmentUseCase DebugBuildEnvironmentUseCase { get; }
#endif  // GLOW_DEBUG

        // スプラッシュの表示時間
        const float AttentionSplashDisplayTime = 2.5f;
        const float DisappearAnimationDuration = 0.3f;
        const float LogoDisplayTime = 1.6f;
        
        UniTaskCompletionSource _tcs;

        void IISplashViewDelegate.OnViewDidLoad()
        {
            // TODO: 設定タイミングを現状Splashに載せているが、どのタイミングユーザー設定をもとに復元/設定するかを検討する

            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {

#if GLOW_DEBUG
                // NOTE: デバッグ時の環境選択
                await DebugEnvironmentSelectorInvoker.Invoke(cancellationToken);

                // NOTE: デバッグ用の環境構築
                DebugBuildEnvironmentUseCase.BuildEnvironment();
#else
                // NOTE: 接続環境などを構築する
                await BuildEnvironmentUseCase.BuildEnvironment(cancellationToken);
#endif //  GLOW_DEBUG
                
                // NOTE: ユーザープロパティを読み込んでセットアップ
                await SetUpUserPropertyUseCase.SetUp(cancellationToken);
                
                // タップスキップの設定
                _tcs = new UniTaskCompletionSource();
                ViewController.SetOnTouchLayerTouched(SetUniTaskCompletionSource);
                
                // 年齢確認注意スプラッシュの表示待機 時間経過かタップで進行
                await UniTask.WhenAny(
                    _tcs.Task, 
                    UniTask.Delay(TimeSpan.FromSeconds(AttentionSplashDisplayTime), cancellationToken: cancellationToken));
                
                
                // ロゴスプラッシュ表示はAnimatorの設定で年齢確認注意スプラッシュの消えるアニメーション再生後に自動再生される
                ViewController.PlayDisappearAttentionSplashAnimation();
                // 年齢確認注意スプラッシュの消えるアニメーションを待機
                await UniTask.Delay(TimeSpan.FromSeconds(DisappearAnimationDuration), cancellationToken: cancellationToken);
                
                // UniTaskCompletionSourceのリセット
                _tcs = new UniTaskCompletionSource();
                // 会社ロゴ表示の待機 時間経過かタップで進行
                await UniTask.WhenAny(
                    _tcs.Task, 
                    UniTask.Delay(TimeSpan.FromSeconds(LogoDisplayTime), cancellationToken: cancellationToken));
                
                SceneNavigation.Switch<MaskTransition>(default, SceneInBuildName.TITLE, "WhiteWipeTransition").Forget();
            });
        }
        
        void SetUniTaskCompletionSource()
        {
            _tcs.TrySetResult();
        }
    }
}
