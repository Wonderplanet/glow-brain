using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants;
using GLOW.Core.Constants.SceneTransition;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.Advertising;
using GLOW.Core.Domain.Modules.Network;
using GLOW.Core.Presentation.Modules.Audio;
using Runtime.PlayerPrefs;
using UIKit;
using UnityEngine;
using UnityEngine.Rendering;
using UnityEngine.SceneManagement;
using WonderPlanet.AnalyticsBridge;
using WonderPlanet.CrashReporterBridge;
using WonderPlanet.CultureSupporter.Time;
using WonderPlanet.ErrorCoordinator;
using WonderPlanet.ObservabilityKit;
using WonderPlanet.ResourceManagement;
using WonderPlanet.SceneManagement;
using WonderPlanet.ToastNotifier;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Application.SceneDelegates;
using WPFramework.Domain.Modules;
using WPFramework.Exceptions;
using WPFramework.Modules.Localization;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Views;
using Zenject;

#if GLOW_DEBUG
using GLOW.Debugs.Command.Presentations;
#endif

namespace GLOW.Core.Application.SceneDelegates
{
    internal sealed class ApplicationDelegate : MonoBehaviour, IInitializable, IBootstrapSceneDelegate, IDisposable
    {
        [SerializeField] string _initialSceneName = "Title";
        [SerializeField] Camera _applicationDefaultCamera;

        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] IErrorCoordinator ErrorCoordinator { get; }
        [Inject] IAssetManagement AssetManagement { get; }
        [Inject] ILocalizationAssetManagement LocalizationAssetManagement { get; }
        [Inject] ILocalizationAssetSource LocalizationAssetSource { get; }
        [Inject] ILocalizationTermsManagement LocalizationTermsManagement { get; }
        [Inject] ILocalizationTermsSource LocalizationTermsSource { get; }
        [Inject] ICustomAssetBundleEncryptKeyProvider CustomAssetBundleEncryptKeyProvider { get; }
        [Inject] ISoundEffectLoader SoundEffectLoader { get; }
        [Inject] IBackgroundMusicManagement BackgroundMusicManagement { get; }
        [Inject] ICalibrationDateTimeSource CalibrationDateTimeSource { get; }
        [Inject] ICalibrationDateTimeOffsetSource CalibrationDateTimeOffsetSource { get; }
        [Inject] AnalyticsCenter AnalyticsCenter { get; }
        [Inject] CrashReportCenter CrashReportCenter { get; }
        [Inject] IReporter ObservabilityKitReporter { get; }
        [Inject] IApiContextBuilder ApiContextBuilder { get; }
        [Inject] IApiContextHeaderBuilder APIContextHeaderBuilder { get; }
        [Inject] ModalPresentationObserver ModalPresentationObserver { get; }
        [Inject] IAdvertisingManager AdvertisingManager { get; }


        public bool IsBootstrapCompleted { get; set; }

        /// <summary>
        /// Zenjectによって呼び出されるアプリケーションのエントリポイント
        /// </summary>
        void IInitializable.Initialize()
        {
            ApplicationLog.Log(nameof(ApplicationDelegate), nameof(IInitializable.Initialize));

            if (IsBootstrapCompleted)
            {
                ApplicationLog.Log(nameof(ApplicationDelegate), $"{nameof(IInitializable.Initialize)}: Already initialized");
                return;
            }

            if (!ErrorCoordinator.Initialize())
            {
                ApplicationLog.LogWarning(
                    nameof(ApplicationDelegate),
                    $"{nameof(IInitializable.Initialize)}: ErrorCoordinator.Initialize failed");
            }

            // TODO: 初期化周りは後ほど整理する

            // NOTE: エラー時にUnityが出すログウィンドウを無効化
            Debug.developerConsoleEnabled = false;

            // NOTE: DataDogに送信するログレベルを設定
            SetObservabilityKitLogLevel();

            // NOTE: GlowEncryptionPlayerPrefsの初期化
            var info = new EncryptionSettingInfo(Credentials.PlayerPrefsPw, Credentials.PlayerPrefsSalt, true);
            EncryptionPlayerPrefs.SetEncryptionSetting(info);

            // NOTE: タッチの設定
            Input.multiTouchEnabled = false;

            // NOTE: フレームレート設定
            QualitySettings.vSyncCount = 0;
            UnityEngine.Application.targetFrameRate = 60;
            // NOTE: APIContextの設定を実施する
            ApiContextBuilder.Build();

            // NOTE: UIKitのモーダルの初期設定を行う
            UIViewController.DefaultModalWindow = "FrameworkModalItemHostingWindow";
            UIViewController.DefaultPresentationStyle = UIModalPresentationStyle.Custom;
            UIViewController.DefaultPresentationDelegate = new ModalPresentationDelegate();
            UIViewController.GlobalModalPresentationEventCallback = ModalPresentationObserver;

            // NOTE: Toastで利用するタップガードを設定する
            Toast.SetToastTapGuardType(TapGuardType.NoBlock);

            // NOTE: Observability Kitの初期化
            ObservabilityKit.Setup(ObservabilityKitReporter);

            DoAsync.Invoke(this, async cancellationToken =>
            {
                // NOTE: 起動時のシーン数が1であるならば、初回起動であると判断する
                var sceneName = _initialSceneName;
                if (SceneManager.sceneCount != 1)
                {
                    // NOTE: 起動シーン以外から起動した際に開いているシーンの登録情報を一度削除して改めて起動させる
                    var lastScene = SceneHelper.GetLastScene();
                    sceneName = lastScene.name;
                    await SceneManager.UnloadSceneAsync(lastScene.name);
                }

                // NOTE: UIViewBundleのモードを指定する
                UIViewBundle.BundleMode = UIViewBundleMode.Addressable;

                // NOTE: Addressablesの初期化
                //       Debugビルドの場合にUIViewBundleをデバッグメニューも利用するためこの段階で初期化を実施する
                await AssetManagement.Initialize(
                    cancellationToken,
                    keyProvider: CustomAssetBundleEncryptKeyProvider);

                // NOTE: Toastで利用するプレハブを設定する(要: AssetManagement.Initializeより後ろ)
                Toast.Load("ToastContents",
                    ToastBundleMode.Addressable,
                    _ => { },
                    (_, e) => ApplicationLog.LogError(nameof(Toast), e.ToString()));

                // NOTE: Localizationの初期化
                await LocalizationAssetManagement.Initialize(cancellationToken);

                // NOTE: Localizationを利用したTermsの初期化と読み込み
                await LocalizationTermsManagement.Initialize(cancellationToken, LocalizationAssetSource);
                await LocalizationTermsManagement.Load(cancellationToken, "system");
                ConditionedNotImplementedHandler.SetTerms(LocalizationTermsSource);

                // NOTE: UIViewBundleの読み込み
                var bundleCompletionSource = new UniTaskCompletionSource();
                UIViewBundle.Main.Load(
                    _ => bundleCompletionSource.TrySetResult(),
                    (_, e) => bundleCompletionSource.TrySetException(e));
                await using var _ = cancellationToken.Register(() => bundleCompletionSource.TrySetCanceled(cancellationToken));
                await bundleCompletionSource.Task;
#if GLOW_DEBUG
                // NOTE: デバッグコマンドを封印する
                DebugCommandActivator.Disable();
#endif  // DEBUG

                // NOTE: スプラッシュスクリーンの機構を利用している場合、終了まで待つようにする
                //       https://docs.unity3d.com/ja/2020.3/ScriptReference/Rendering.SplashScreen-isFinished.html
                await UniTask.WaitUntil(() => SplashScreen.isFinished, cancellationToken: cancellationToken);

                // NOTE: 言語変更を反映させるためリクエストヘッダの再設定
                //       Addressablesから情報を反映させるため初期化後に設定を行う
                await APIContextHeaderBuilder.Build(cancellationToken);

                // NOTE: 共通サウンドの読み込み
                await SoundEffectLoader.Load(cancellationToken, SoundEffectTag.Common);

                await UniTask.WhenAll(
                    BackgroundMusicManagement.Load(cancellationToken, BGMAssetKeyDefinitions.BGM_title),
                    BackgroundMusicManagement.Load(cancellationToken, BGMAssetKeyDefinitions.BGM_home),
                    BackgroundMusicManagement.Load(cancellationToken, BGMAssetKeyDefinitions.BGM_quest_content_top)
                );

                // NOTE: 日付管理機構を設定する
                TimeProvider.SetDateTimeSource(CalibrationDateTimeSource);
                TimeProvider.SetDateTimeOffsetSource(CalibrationDateTimeOffsetSource);

                // NOTE: 分析基盤の初期化
                await AnalyticsCenter.Initialize(cancellationToken);
                // NOTE: クラッシュレポート基盤の初期化
                await CrashReportCenter.Initialize(cancellationToken);

                // NOTE: 広告基盤の初期化を行う
                InitializeIAA();

                // NOTE: 報酬広告の読み込みを行う
                //       ここで読み込んでおくことで、広告表示時に待たされる時間を短縮する
                //       外部から利用するCancellationTokenはデフォルト値を使用する
                AdvertisingManager.LoadAndCacheRewardedAd(cancellationToken: default);

                // NOTE: ブート完了を設定
                IsBootstrapCompleted = true;

                // NOTE: シーン切明を行う
                await SceneNavigation.Switch<MaskTransition>(
                    default,
                    sceneName: sceneName,
                    transitionVariant: SceneTransitionVariant.ApplicationTransition);

                // NOTE: 画面のちらつきを無くすためのカメラを除去する
                //       防止用のカメラはシーン切り替え後に削除する
                DisableApplicationDefaultCamera();

                ApplicationLog.Log(nameof(ApplicationDelegate), $"{nameof(IInitializable.Initialize)}: Finished");
            });
        }

        void InitializeIAA()
        {
            if (!AdvertisingManager.IsInitialized)
            {
                AdvertisingManager.Initialize();
            }
        }

        void OnApplicationFocus(bool hasFocus)
        {
            if (!hasFocus)
            {
                return;
            }

#if UNITY_IOS
            // NOTE: バッジ表示を消す
            //       ローカル通知もリモート通知も共通
            Unity.Notifications.iOS.iOSNotificationCenter.ApplicationBadge = 0;
#endif  // UNITY_IOS
        }

        void SetObservabilityKitLogLevel()
        {
#if GLOW_DEBUG
            ObservabilityKit.LogLevel = ObservabilityKitLogLevel.Debug;
#else
            ObservabilityKit.LogLevel = ObservabilityKitLogLevel.Production;
#endif
        }

        void DisableApplicationDefaultCamera()
        {
            if (!_applicationDefaultCamera)
            {
                return;
            }

            Destroy(_applicationDefaultCamera.gameObject);
            _applicationDefaultCamera = null;

            ApplicationLog.Log(nameof(ApplicationDelegate), "disable application default camera");
        }

        void IDisposable.Dispose()
        {
            // NOTE: アプリケーション終了後にUIViewBundleのリソースを解放する
            UIViewBundle.Release();

            // NOTE: 広告の破棄を行う
            AdvertisingManager.DestroyAdAll();

            UIViewController.DefaultModalWindow = null;
            UIViewController.DefaultPresentationStyle = default;
            UIViewController.DefaultPresentationDelegate = null;
            UIViewController.GlobalModalPresentationEventCallback = null;
        }
    }
}
