using System;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using WonderPlanet.SceneManagement;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Exceptions;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Modules.Systems;
using GLOW.Core.Presentation.Transitions;
using GLOW.Debugs.Command.Presentations;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.Tutorial.Domain.AssetDownloader;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Scenes.AgreementDialog.Presentation.Views;
using GLOW.Scenes.AnnouncementWindow.Domain.UseCase;
using GLOW.Scenes.AppTrackingTransparencyConfirm.Presentation.Views;
using GLOW.Scenes.AssetDownloadNotice.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Login.Domain.Constants.Login;
using GLOW.Scenes.Login.Domain.UseCase;
using GLOW.Scenes.Login.Domain.UseCases;
using GLOW.Scenes.Login.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.TermsOfService.Presentation.Views;
using GLOW.Scenes.Title.Domains.UseCase;
using GLOW.Scenes.Title.Presentations.ViewModels;
using GLOW.Scenes.Title.Presentations.Views;
using GLOW.Scenes.Title.Presentations.WireFrame;
using GLOW.Scenes.UserNameEdit.Presentation.Views;
using GLOW.Scenes.TitleMenu.Presentation;
using Wonderplanet.IAP.Exception;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.Title.Presentations.Presenters
{
    public sealed class TitlePresenter : ITitleViewDelegate
        , ILoginPresentUserApproval
        , ILoginPhaseNotifier
        , ILoginTrackingTransparencyApproval
        , ITutorialAssetDownloadPresentUserApproval
    {
        [Inject] TitleViewController ViewController { get; }
        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] LoginUseCases LoginUseCases { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] IGetApplicationInfoUseCase GetApplicationInfoUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IApplicationTerminator ApplicationTerminator { get; }
        [Inject] ISelectStageUseCase SelectStageUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ITutorialAssetDownloader TutorialAssetDownloader { get; }
        [Inject] IsUserDataCreatedUseCase IsUserDataCreatedUseCase { get; }
        [Inject] TutorialIntroductionStageStartUseCase StartIntroductionStageUseCase { get; }
        [Inject] ShouldTutorialDownloadUseCase ShouldTutorialDownloadUseCase { get; }
        [Inject] ShouldTutorialSetNameUseCase ShouldTutorialSetNameUseCase { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] CheckAllAnnouncementReadUseCase CheckAllAnnouncementReadUseCase { get; }
        [Inject] InitializeIAPUseCase InitializeIAPUseCase { get; }
        [Inject] InitializePassEffectUseCase InitializePassEffectUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] TutorialBackGroundDownloadUseCase TutorialBackGroundDownloadUseCase { get; }
        [Inject] TitleWireFrame TitleWireFrame { get; }
        [Inject] IContentMaintenanceCoordinator ContentMaintenanceCoordinator { get; }
        [Inject] IContentMaintenanceHandler ContentMaintenanceHandler { get; }

#if GLOW_DEBUG
        [Inject] CompleteFreePartTutorialUseCase CompleteFreePartTutorialUseCase { get; }
        [Inject] IDeferredPurchaseCacheRepository DeferredPurchaseCacheRepository { get; }
#endif

        CancellationToken CancellationToken => ViewController.View.GetCancellationTokenOnDestroy();


        void ITitleViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(TitlePresenter), nameof(ITitleViewDelegate.OnViewDidLoad));
            ContentMaintenanceCoordinator.SetUp(ContentMaintenanceHandler);

            // 遷移の設定
            ViewController.SetOnTouchLayerTouched(()=>
            {
                // タイトル画面への連打防止のためnullにする
                ViewController.SetOnTouchLayerTouched(null);
                TitleWireFrame.SwitchHomeScene();
                LoginUseCases.ChangeTransitToHomePhase();
            });


            SetVersionAndMyId();

            DoAsync.Invoke(CancellationToken, async (cancellationToken) =>
            {
                BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_title);

                ViewController.PlayInAnimation();

                // NOTE: ログイン処理の進捗を通知する
                // TODO: どこのチャンク処理を実行中かも扱えるインタフェースに拡張する
                var progress = new Progress<float>(progress =>
                {
                    // NOTE: ViewControllerがロードされていない場合は何もしない
                    if (!ViewController.IsViewLoaded)
                    {
                        return;
                    }

                    var loginProgressViewModel = new LoginProgressViewModel(progress);
                    ViewController.SetProgress(loginProgressViewModel);
                });

                try
                {
                    await LoginUseCases.Login(cancellationToken, progress);
                }
                catch (LicenseAgreementPermissionRefusedException)
                {
                    // NOTE: ライセンス同意が拒否された場合は再起動
                    ApplicationRebootor.Reboot();
                    return;
                }
                catch (AssetBundleDownloadPermissionRefusedException)
                {
                    // NOTE: アセットバンドルのダウンロードが拒否された場合は再起動
                    ApplicationRebootor.Reboot();
                    return;
                }
                catch (AssetBundleContentCatalogUpdateFailedException)
                {
                    // NOTE: コンテンツカタログのダウンロードのリトライを断った場合は再起動
                    ApplicationRebootor.Reboot();
                    return;
                }
                catch (AssetBundleDownloadFailedException)
                {
                    // NOTE: アセットバンドルのダウンロードのリトライを断った場合は再起動
                    ApplicationRebootor.Reboot();
                    return;
                }
                catch (MstDataDownloadPermissionRefusedException)
                {
                    // NOTE: マスターデータのダウンロードが拒否された場合は何もしない
                    return;
                }

                try
                {
                    await InitializeIAPUseCase.Initialize(cancellationToken);
                }
                catch (IAPStoreInitializeException e)
                {
                    // NOTE: 購入可能な商品がない場合は何もしない
                    if (IAPStoreInitializeExceptionReason.NoProductsAvailable != e.Reason)
                    {
                        var message = e.Reason switch
                        {
                            IAPStoreInitializeExceptionReason.ProviderUnavailable =>
                                "決済機能の初期化に失敗しました。\n端末・アカウント設定などを確認してください。",
                            _ => "決済機能の初期化に失敗しました。",
                        };
                        var isClose = false;
                        MessageViewUtil.ShowMessageWithClose("初期化エラー", message, onClose: () => isClose = true);
                        await UniTask.WaitUntil(() => isClose, cancellationToken: cancellationToken);
                    }
                }
                finally
                {
                    InitializePassEffectUseCase.InitializeValidPassEffect();
                }

                // お知らせの最終更新日時を取得(最終的にログイン後にタイトルでお知らせを出すのを見越しての対応)
                var alreadyReadAllAnnouncement =
                    await CheckAllAnnouncementReadUseCase.GetAllAnnouncementAlreadyRead(cancellationToken);

                // TODO: メンテの場合などは専用のビューを出すようにする

                // NOTE: タップ時の遷移先設定
                if (ShouldTutorialDownloadUseCase.ShouldTutorialDownload())
                {
                    // 導入パートの間のみバックグラウンドダウンロードを行う
                    ViewController.SetOnTouchLayerTouched(() =>
                    {
                        DoAsync.Invoke(CancellationToken, ScreenInteractionControl, async (ct) =>
                        {
                            await TutorialAction(ct);
                            LoginUseCases.ChangeTransitToHomePhase();
                        });
                    });
                }
                else
                {
                    SetVersionAndMyId();
                }

                // 全てのローカル通知の再設定
                // アプリ起動からn時間後に通知するローカル通知再設定
                LocalNotificationScheduler.Initialize();
                LocalNotificationScheduler.RefreshAllSchedules();

                // Loading完了 タップ判定を有効にする
                ViewController.OnEndLoading();

                ViewController.SetMenuButtonNotificationBadge(alreadyReadAllAnnouncement.ToNotificationBadge());

                // NOTE: インゲームの再開を試みる
                // Home or InGameの遷移があるので最後にしている
                await LoginUseCases.CheckCurrentPlaySession(cancellationToken);

#if GLOW_DEBUG
                DebugCommandActivator.OnDebugCommandActivated += DebugCommandActivated;
#endif
            });
        }

        void ITitleViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(TitlePresenter), nameof(ITitleViewDelegate.OnViewDidUnload));
#if GLOW_DEBUG
            DebugCommandActivator.OnDebugCommandActivated -= DebugCommandActivated;
#endif
        }

        async UniTask<bool> ILoginPresentUserApproval.PresentUserWithAgreementScreenAndCheckResult(
            CancellationToken cancellationToken,
            GameVersionModel gameVersionModel)
        {
            var completionSource = new UniTaskCompletionSource<bool>();
            var controller = ViewFactory.Create<TermsOfServiceViewController, TermsOfServiceViewController.Argument>(
                new TermsOfServiceViewController.Argument(
                    () => completionSource.TrySetResult(true),
                    () => completionSource.TrySetResult(false)));
            ViewController.PresentModally(controller);

            // 許諾処理に対して結果が来るまで待機する
            await using var _ =
                cancellationToken.Register(
                    () => completionSource.TrySetCanceled(),
                    useSynchronizationContext: true);

            return await completionSource.Task;
        }

        async UniTask<bool> ILoginPresentUserApproval.PresentUserWithAgreementModuleScreenAndCheckResult(
            CancellationToken cancellationToken,
            AgreementUrl agreementUrl)
        {
            var completionSource = new UniTaskCompletionSource<bool>();
            var controller = ViewFactory.Create<AgreementDialogViewController, AgreementDialogViewController.Argument>(
                new AgreementDialogViewController.Argument(
                    () => completionSource.TrySetResult(true),
                    agreementUrl));
            ViewController.PresentModally(controller);

            // 許諾処理に対して結果が来るまで待機する
            await using var _ =
                cancellationToken.Register(
                    () => completionSource.TrySetCanceled(),
                    useSynchronizationContext: true);
            return await completionSource.Task;
        }

        async UniTask<bool> ILoginPresentUserApproval.PresentUserWithMstDataDownloadScreenAndCheckResult(
            CancellationToken cancellationToken,
            DownloadMetricsUseCaseModel downloadMetricsUseCaseModel)
        {
            // マスターデータのDLは別でダイアログを出したりしない
            return await UniTask.FromResult(true);
        }

        async UniTask<bool> ILoginPresentUserApproval.PresentUserWithAssetBundleDownloadScreenAndCheckResult(
            CancellationToken cancellationToken,
            DownloadMetricsUseCaseModel downloadMetricsUseCaseModel)
        {
            return await ShowAndWaitAssetDownloadNotice(cancellationToken, downloadMetricsUseCaseModel.TotalBytes);
        }

        async UniTask<bool> ShowAndWaitAssetDownloadNotice(CancellationToken cancellationToken, AssetDownloadSize totalBytes)
        {
            // NOTE: アセットダウンロード確認画面を表示する
            var completionSource = new UniTaskCompletionSource<bool>();
            var controller = ViewFactory.Create<AssetDownloadNoticeViewController, AssetDownloadNoticeViewController.Argument>(
                new AssetDownloadNoticeViewController.Argument(
                    DownloadSize: totalBytes,
                    Download: () => completionSource.TrySetResult(true),
                    Cancel: () => completionSource.TrySetResult(false)));
            ViewController.PresentModally(controller);

            // ダイアログ操作待ち
            await using var _ =
                cancellationToken.Register(
                    () => completionSource.TrySetCanceled(),
                    useSynchronizationContext: true);
            return await completionSource.Task;
        }

        async UniTask ILoginPresentUserApproval.PresentUserWithFreeSpaceError(CancellationToken cancellationToken)
        {
            await ShowAndWaitLimitedStorageCapacity(cancellationToken);
        }

        async UniTask<bool> ILoginPresentUserApproval.PresentUserWithAssetBundleRetryableDownload(
            CancellationToken cancellationToken)
        {

            return await ShowAndWaitRetryableDownload(cancellationToken);
        }

        async UniTask<bool> ShowAndWaitRetryableDownload(CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource<bool>();
            MessageViewUtil.ShowConfirmMessage(
                title: Terms.Get("login_progress_message_asset_download_other_title"),
                message: Terms.Get("login_progress_message_asset_download_other_message"),
                attentionMessage: string.Empty,
                onOk: () => completionSource.TrySetResult(true),
                onCancel: () => completionSource.TrySetResult(false));

            // ダイアログ操作待ち
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());
            return await completionSource.Task;
        }

        async UniTask<bool> ShowAndWaitLimitedStorageCapacity(CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource<bool>();

            MessageViewUtil.ShowMessageWithOk(
                "容量不足エラー",
                "ダウンロードに必要な容量が不足しています。",
                String.Empty,
                () => completionSource.TrySetResult(true));

            // ダイアログ操作待ち
            await using var _ =
                cancellationToken.Register(
                    () => completionSource.TrySetCanceled(),
                    useSynchronizationContext: true);
            return await completionSource.Task;
        }

        async UniTask<bool> ITutorialAssetDownloadPresentUserApproval.PresentUserWithAssetBundleDownloadScreenAndCheckResult(
            CancellationToken cancellationToken,
            AssetDownloadSize downloadSize,
            FreeSpaceSize freeSpaceSize)
        {
            var completionSource = new UniTaskCompletionSource<bool>();

            // NOTE: 後でボタンorバックキーの共通処理
            Action cancelAction = () =>
            {
                // NOTE: 後でボタンorバックキーを押した場合はダウンロードせず、後ほど再表示
                ApplicationLog.Log(nameof(TutorialAssetDownloader), $"後で");
                completionSource.TrySetResult(false);
            };

            // ダウンロードダイアログ表示を汎用ダイアログでする
            MessageViewUtil.ShowMessageWith2Buttons(
                title: "データダウンロード",
                message: ZString.Format("ゲームデータをダウンロードしながら\n" +
                                        "チュートリアルを始められます。\n\n" +
                                        "ダウンロードを開始してよろしいですか？\n" +
                                        "(サイズ <color=#EE3628>{0}</color>)", downloadSize.ToStringSeparated()),
                attentionMessage: "※通信状況の良い場所で\n" +
                                  "ダウンロードしてください。\n" +
                                  "Wi-Fiでのダウンロードをお勧めします。",
                option1ButtonTitle: "ダウンロード",
                option2ButtonTitle: "後で",
                action1: () =>
                {
                    // NOTE: ダウンロードボタンを押した場合はダウンロード開始する
                    ApplicationLog.Log(nameof(TutorialAssetDownloader), $"ダウンロード");
                    completionSource.TrySetResult(true);
                },
                action2: cancelAction,
                onClose: cancelAction);

            // タイトル画面への連打防止のためチュートリアルダウンロードダイアログ表示後はnullにする
            ViewController.SetOnTouchLayerTouched(null);

            // ダイアログ操作待ち
            await using var _ =
                cancellationToken.Register(
                    () => completionSource.TrySetCanceled(),
                    useSynchronizationContext: true);

            return await completionSource.Task;
        }

        async UniTask ITutorialAssetDownloadPresentUserApproval.PresentUserWithFreeSpaceError(CancellationToken cancellationToken)
        {
            await ShowAndWaitLimitedStorageCapacity(cancellationToken);
        }

        async UniTask<bool> ITutorialAssetDownloadPresentUserApproval.PresentUserWithAssetBundleRetryableDownload(
            CancellationToken cancellationToken)
        {
            return await ShowAndWaitRetryableDownload(cancellationToken);
        }

        void ITitleViewDelegate.OnEscapeSelected(LoginPhases loginPhases)
        {
            switch (loginPhases)
            {
                case LoginPhases.None:
                case LoginPhases.Complete:
                    MessageViewUtil.ShowMessageWith2Buttons(
                        "アプリ終了確認",
                        "アプリを終了しますか？",
                        "",
                        "アプリ終了",
                        "キャンセル",
                        () => ApplicationTerminator.Terminate(),
                        () => { },
                        () => { });
                    break;
                case LoginPhases.FetchAssetBundleManifest:
                case LoginPhases.FetchAssetBundle:
                case LoginPhases.TransitionToHome:
                    // NOTE: アセットバンドルのダウンロード中は「操作不可」を表示する
                    CommonToastWireFrame.ShowInvalidOperationMessage();
                    break;
                default:
                    //仮で入れている
                    CommonToastWireFrame.ShowInvalidOperationMessage();
                    break;
            }
        }

        void ITitleViewDelegate.OnMenuSelected()
        {
            var argument = new TitleMenuViewController.Argument(ViewController.ActualView.AlreadyReadAnnouncementFlag);
            var controller = ViewFactory.Create<TitleMenuViewController, TitleMenuViewController.Argument>(argument);
            controller.OnClosedAction = (alreadyReadAllAnnouncement) =>
            {
                ViewController.SetMenuButtonNotificationBadge(alreadyReadAllAnnouncement.ToNotificationBadge());
            };
            ViewController.PresentModally(controller);
        }

        void ILoginPhaseNotifier.LoginPhaseChanged(LoginPhaseLabel loginPhaseLabel)
        {
            var message = string.Empty;
            switch (loginPhaseLabel.LoginPhase)
            {
                default:
                case LoginPhases.None:
                    break;
                case LoginPhases.LicenseAgreement:
                    message = Terms.Get("login_progress_message_license");
                    break;
                case LoginPhases.FetchServerTime:
                case LoginPhases.FetchGameVersion:
                case LoginPhases.FetchMstDataManifest:
                case LoginPhases.FetchMstData:
                case LoginPhases.LoadMstData:
                case LoginPhases.FetchUserData:
                    message = Terms.Get("login_progress_message_fetch_data");
                    break;
                case LoginPhases.Authenticate:
                    message = Terms.Get("login_progress_message_authentication");
                    break;
                case LoginPhases.SDKInitialize:
                    message = Terms.Get("login_progress_message_initialize");
                    break;
                case LoginPhases.FetchAssetBundleManifest:
                case LoginPhases.FetchAssetBundle:
                    message = Terms.Get("login_progress_message_download_asset_bundle");
                    break;
                case LoginPhases.Complete:
                case LoginPhases.TransitionToHome:
                    message = Terms.Get("login_progress_message_complete");
                    break;
            }

            ViewController.SetLoginPhase(new LoginPhaseViewModel(message, loginPhaseLabel.LoginPhase));
        }

        async UniTask ILoginTrackingTransparencyApproval.ShowTrackingTransparencyConfirmView()
        {
            var vc = ViewFactory.Create<AppTrackingTransparencyConfirmViewController>();
            ViewController.PresentModally(vc);
            await UniTask.WaitUntil(vc.View.IsDestroyed);
        }

        public void LoginPhaseDetailChanged(LoginPhaseDetailLabel loginPhaseDetailLabel)
        {
        }

        public void LoginPhaseDetailEnded()
        {
        }

        async UniTask TutorialAction(CancellationToken cancellationToken)
        {
            // 名前決定が必要な場合、名前設定を行う
            if (await ShouldTutorialSetNameUseCase.ShouldTutorialSetName(cancellationToken))
            {
                // ユーザー名の設定
                var controller = ViewFactory.Create<UserNameEditDialogViewController>();
                controller.OnConfirmed = () =>
                {
                    // バックグラウンドダウンロード作成・確認
                    DoTutorialAssetDownload();
                };
                ViewController.PresentModally(controller);
                return;
            }

            // 名前決定が必要ない場合 バックグラウンドダウンロード作成・確認のみ行う
            DoTutorialAssetDownload();
        }

        void SwitchTutorialInGameScene()
        {
            DoAsync.Invoke(CancellationToken, ScreenInteractionControl,async (cancellationToken) =>
            {
                var mstStageId = TutorialDefinitionIds.Stage1Id;
                SelectStageUseCase.SelectStage(mstStageId, MasterDataId.Empty, ContentSeasonSystemId.Empty);
                await StartIntroductionStageUseCase.StartStage(cancellationToken, mstStageId);
                // 意図的にHomeTopTransitionを使用しています
                SceneNavigation.Switch<HomeTopTransition>(default, "InGame").Forget();
            });
        }

        void SetVersionAndMyId()
        {
            var applicationInfoModel = GetApplicationInfoUseCase.GetApplicationInformation();
            var applicationInfoViewModel = new ApplicationInfoViewModel(
                applicationInfoModel.ApplicationVersion,
                applicationInfoModel.UserMyId);

            // NOTE: 端末保存されていた場合は最初からユーザーIDを表示する
            ViewController.SetUserMyId(applicationInfoViewModel.UserMyId);

            // NOTE: アプリケーションの情報を表示
            ViewController.SetApplicationInfo(applicationInfoViewModel);
        }

        void DoTutorialAssetDownload()
        {
            // バックグラウンドダウンロード作成・確認
            Action downloadConfirmedAction = SwitchTutorialInGameScene;
            Action downloadRefusedAction = SwitchTutorialInGameScene;
            TutorialAssetDownloader.SetPresentUserApproval(this);

            TutorialAssetDownloader.DoBackgroundAssetDownload(
                downloadConfirmedAction,
                downloadRefusedAction,
                TutorialBackGroundDownloadUseCase.GetGameVersion());

        }

# if GLOW_DEBUG
        void DebugCommandActivated(IDebugCommandPresenter debugCommandPresenter)
        {
            ApplicationLog.Log(nameof(HomePresenter), nameof(DebugCommandActivated));

            debugCommandPresenter.CreateRootMenu = CreateDebugCommandRootMenu;
        }

        void CreateDebugCommandRootMenu(IDebugCommandPresenter debugCommandPresenter)
        {
            debugCommandPresenter.AddButton(
                "チュートリアル フリーパート全完了",
                CompleteFreePartTutorial);
            debugCommandPresenter.AddButton(
                "リストアエラー追加",
                () =>
                {
                    DeferredPurchaseCacheRepository.AddDeferredPurchaseErrorCode(DeferredPurchaseErrorCode.RestoreFailed);
                });
        }

        void CompleteFreePartTutorial()
        {
            using var cancellationTokenSource = new CancellationTokenSource();
            var cancellationToken = cancellationTokenSource.Token;
            DoAsync.Invoke(cancellationToken, ScreenInteractionControl, async ct =>
            {
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.ReleaseEventQuest);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.ReleaseAdventBattle);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.ReleaseHardStage);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.ReleasePvp);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.ReleaseEnhanceQuest);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.OutpostEnhance);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.SpecialUnit);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.IdleIncentive);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.ArtworkFragment);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct,
                    TutorialFreePartIdDefinitions.TransitPvp);
            });
        }
#endif
    }
}
