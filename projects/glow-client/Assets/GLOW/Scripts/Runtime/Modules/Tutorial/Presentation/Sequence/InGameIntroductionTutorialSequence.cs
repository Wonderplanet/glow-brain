using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.Tutorial.Domain.AssetDownloader;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Modules.TutorialTapIcon.Presentation.ValueObject;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.AssetDownloadNotice.Presentation.Views;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Views.InGameUnitDetail;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Presentation.Components;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    /// <summary>
    /// 導入パートチュートリアル
    /// チュートリアルステージ1 導入パート
    /// </summary>
    public class InGameIntroductionTutorialSequence : 
        BaseInGameTutorialSequence, 
        ITutorialAssetDownloadPresentUserApproval
    {
        [Inject] ITutorialAssetDownloader TutorialAssetDownloader { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] SkipIntroductionTutorialUseCase SkipIntroductionTutorialUseCase { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] TutorialBackGroundDownloadUseCase TutorialBackGroundDownloadUseCase { get; }
        
        CancellationTokenSource _cancellationTokenSource;
        bool _isPause = false;
        DownloadProgress _downloadProgress = new DownloadProgress(0);
        bool _isDisplayAnyDialog;
        bool _isInternalCancellation = false;
        
        public override async UniTask Play(CancellationToken token)
        {
            _isInternalCancellation = false;
            _cancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(token);
            var cancellationToken = _cancellationTokenSource.Token;
            
            try
            {
                // チュートリアル開始
                var tipModels = GetTutorialTips(new MasterDataId("TutorialStartIntroduction"));
                
                // ダウンロード表示設定
                SetupTutorialDownloadIfNeeds();
                DisplayTutorialDownloadIfNeeds();
                
                // ポーズ状態から開始
                _isPause = true;

                // ユニットアイコンのボタンを無効化
                var button1 = FindTargetObject("chara_button_1").GetComponent<Button>();
                var button2 = FindTargetObject("chara_button_2").GetComponent<Button>();
                var button3 = FindTargetObject("chara_button_3").GetComponent<Button>();
                var button4 = FindTargetObject("chara_button_4").GetComponent<Button>();
                var button5 = FindTargetObject("chara_button_5").GetComponent<Button>();
                
                button1.enabled = false;
                button2.enabled = false;
                button3.enabled = false;
                button4.enabled = false;
                button5.enabled = false;

                // 長押しボタン無効化
                var longPressButton1 = FindTargetObject("chara_button_1").GetComponent<UIButtonLongPress>();
                var longPressButton2 = FindTargetObject("chara_button_2").GetComponent<UIButtonLongPress>();
                var longPressButton3 = FindTargetObject("chara_button_3").GetComponent<UIButtonLongPress>();
                var longPressButton4 = FindTargetObject("chara_button_4").GetComponent<UIButtonLongPress>();
                var longPressButton5 = FindTargetObject("chara_button_5").GetComponent<UIButtonLongPress>();
                
                longPressButton1.enabled = false;
                longPressButton2.enabled = false;
                longPressButton3.enabled = false;
                longPressButton4.enabled = false;
                longPressButton5.enabled = false;
                
                // インゲーム開始まで待機する
                await AwaitStartInGame(cancellationToken);
                ShowSkipButton();
                SetPlayingTutorial(true);
                
                await PauseInGame(cancellationToken);
                await FadeInGrayOut(cancellationToken);

                // 味方ゲートのコマをハイライト
                HighlightTarget("outpost_koma");    // KomaComponent_2プレハブの右コマ
               
                await ShowTutorialText(cancellationToken, "これがヒーローゲートだ！\nヒーローゲートのHPが\n0になったら敗北だよ！", -100);
                await HideTutorialText(cancellationToken);
                UnHighlightTarget();
                
                // 再開して敵が出るまで画面明るくする
                await FadeOutGrayOut(cancellationToken);
                await ResumeInGame(cancellationToken);

                await CustomDelay(cancellationToken, 1500);

                // 敵が出てきたので停止して、再度画面を暗くする
                await PauseInGame(cancellationToken);
                await FadeInGrayOut(cancellationToken);

                await ShowTutorialText(cancellationToken, "「ファントム」の侵攻が始まった…！", 0);
                await ShowTutorialText(cancellationToken, "今回は強いキャラたちが\n助けに来てくれたよ！", 0);
                await ShowTutorialText(cancellationToken, "「キャラ」を召喚して\n「ファントム」と戦おう！", 0);
                await ShowTutorialText(cancellationToken, "「キャラ」の召喚にはリーダーPを使うよ", 0);
                
                ShowArrowIndicator("cost_point", ReverseFlag.False);
                await ShowTutorialText(cancellationToken, "召喚に必要なリーダーPはここで見れるよ", 0);
                HideArrowIndicator();
                
                ShowArrowIndicator("leader_point", ReverseFlag.False);
                await ShowTutorialText(cancellationToken, "使えるリーダーPは時間で増えるよ", 0);
                await ShowTutorialText(cancellationToken, "今回はリーダーPをMAXにしたよ！", 0);
                await ShowTutorialText(cancellationToken, "「キャラ」を召喚しよう！", 0);
                await HideTutorialText(cancellationToken);
                HideArrowIndicator();
                button1.enabled = true;
                ShowIndicator("chara_button_1");
                await WaitClickEvent(cancellationToken, "chara_button_1");
                HideIndicator();
                button1.enabled = false;

                // 召喚して少し動かす
                await FadeOutGrayOut(cancellationToken);
                await ResumeInGame(cancellationToken);
                
                // 敵キャラに攻撃するまで待つ
                await CustomDelay(cancellationToken, 3100);

                // 停止して、画面を暗くする
                await PauseInGame(cancellationToken);
                await FadeInGrayOut(cancellationToken);
                
                HighlightTarget("upper_left_koma"); // KomaComponent_2プレハブの左コマ
                ShowArrowIndicator("unit_hp_gauge", ReverseFlag.False);
                await ShowTutorialText(cancellationToken, "「キャラ」は、体力ゲージがなくなると\nやられちゃうから気をつけて！", 0);
                await HideTutorialText(cancellationToken);
                HideArrowIndicator();
                
                // 少し動かす
                await FadeOutGrayOut(cancellationToken);
                await ResumeInGame(cancellationToken);
                
                // 敵キャラにもう一度攻撃するまで待つ
                await CustomDelay(cancellationToken, 2500);

                // 停止して、画面を暗くする
                await PauseInGame(cancellationToken);
                await FadeInGrayOut(cancellationToken);
                
                
                // 必殺技チュートリアル
                ShowArrowIndicator("special_attack_gauge", ReverseFlag.False);
                await ShowTutorialText(cancellationToken, "必殺ゲージがたまると\n「必殺ワザ」を使えるよ！", 0);
                await ShowTutorialText(cancellationToken, "今回は必殺ゲージをMAXにするよ！", 0);
                await HideTutorialText(cancellationToken);
                
                // 1体目のキャラの初回必殺ワザクールタイムを0にする
                SetFirstUnitSpecialAttackCoolTimeToZero();
                
                // 必殺アイコンが表示されるまで1F待つ
                await UniTask.Delay(1, cancellationToken: cancellationToken);
                HideArrowIndicator();
                ShowArrowIndicator("special_attack_icon", ReverseFlag.False);
                
                await ShowTutorialText(cancellationToken, "必殺ワザを使ってみよう！", 0);
                await HideTutorialText(cancellationToken);
                HideArrowIndicator();
                
                button1.enabled = true;
                
                ShowIndicator("chara_button_1");
                await WaitClickEvent(cancellationToken, "chara_button_1");
                
                HideIndicator();
                // 再開して、画面を明るくする
                await FadeOutGrayOut(cancellationToken);
                await ResumeInGame(cancellationToken);

                await CustomDelay(cancellationToken, 6500);

                // チュートリアル表示のためインゲーム停止
                await PauseInGame(cancellationToken);
                await FadeInGrayOut(cancellationToken);
                ShowArrowIndicator("special_attack_gauge", ReverseFlag.False);
                await ShowTutorialText(cancellationToken, "必殺ゲージがもう1回たまるまで\n必殺ワザは使えないよ！", 0);
                await ShowTutorialText(cancellationToken, "2回目からはゲージが\nたまりにくくなるから覚えておこう！", 0);
                await HideTutorialText(cancellationToken);
                HideArrowIndicator();
                
                // 再開して、画面を明るくする
                await FadeOutGrayOut(cancellationToken);
                await ResumeInGame(cancellationToken);
                
                
                // 属性チュートリアル
                // 青属性の敵出現を待つ
                await CustomDelay(cancellationToken, 2200);
                await PauseInGame(cancellationToken);
                await FadeInGrayOut(cancellationToken);
                
                await ShowTutorialText(cancellationToken, "さらに「キャラ」には\n「属性」と「ロール」があるよ", 0f);
                await HideTutorialText(cancellationToken);

                // ダイアログ表示 属性について1
                ShowTutorialDialogWithNextButton(tipModels[0].Title, tipModels[0].AssetPath);
                await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

                // ダイアログ表示 属性について2
                ShowTutorialDialogWithNextButton(tipModels[1].Title, tipModels[1].AssetPath);
                await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

                // ダイアログ表示 ロールについて1
                ShowTutorialDialogWithNextButton(tipModels[2].Title, tipModels[2].AssetPath);
                await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

                // ダイアログ表示 ロールについて2
                ShowTutorialDialog(tipModels[3].Title, tipModels[3].AssetPath);
                await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);
                
                // 出撃キャラの属性・ロールのハイライト
                ShowArrowIndicator("unit_role_and_attribute", ReverseFlag.False);
                await ShowTutorialText(cancellationToken, "「キャラ」の属性とロールは\nここで確認しよう！", 0f);
                await HideTutorialText(cancellationToken);
                HideArrowIndicator();

                // 属性相性のハイライト
                ShowArrowIndicator("attribute_correlation", ReverseFlag.True);
                await ShowTutorialText(cancellationToken, "属性相性はここで確認だ！", 0f);
                await HideTutorialText(cancellationToken);
                HideArrowIndicator();
                
                // 青属性のファントムHPに矢印表示
                HighlightTarget("under_koma");
                ShowArrowIndicator("enemy_hp_gauge", ReverseFlag.False, IsTargetEnemy);
                await ShowTutorialText(cancellationToken, "青属性のファントムがいるよ！", -380f);
                await ShowTutorialText(cancellationToken, "相性が有利な黄属性のキャラを召喚しよう！", -380f);
                await HideTutorialText(cancellationToken);
                HideArrowIndicator();
                
                button2.enabled = true;
                ShowIndicator("chara_button_2");
                await WaitClickEvent(cancellationToken, "chara_button_2");
                button2.enabled = false;
                HideIndicator();
                
                // 再開して、画面を明るくする
                await FadeOutGrayOut(cancellationToken);
                await ResumeInGame(cancellationToken);
                
                // ロイドの攻撃まで待ってから暗転
                await CustomDelay(cancellationToken, 4600);
                await PauseInGame(cancellationToken);
                await FadeInGrayOut(cancellationToken);
                
                // 有利属性の説明テキスト
                HighlightTarget("under_koma");
                HighlightTarget("manga_effect_layer");
                await ShowTutorialText(cancellationToken, "有利な相性のキャラで攻撃すると\n与えるダメージがUPするよ！", -380f);
                await ShowTutorialText(cancellationToken, "有利な相性だと\n文字の色が変わるから目安にしてね", -380f);
                await HideTutorialText(cancellationToken);
                UnHighlightTarget();
                
                // 再開して、画面を明るくする
                await FadeOutGrayOut(cancellationToken);
                await ResumeInGame(cancellationToken);
                
                // ロイドの必殺ゲージ溜まるくらいのタイミングまで待ってから暗転
                await CustomDelay(cancellationToken, 2800);
                await PauseInGame(cancellationToken);
                await FadeInGrayOut(cancellationToken);
                
                
                // キャラ詳細チュートリアル
                SetPlayingUnitDetailTutorial(true);
                await ShowTutorialText(cancellationToken, "必殺ワザや属性などの情報は\nキャラアイコン長押しで確認できるよ", 0);
                await HideTutorialText(cancellationToken);
                
                ShowLongTapIndicator("chara_button_1");
                longPressButton1.enabled = true;
                var uiViewController = await WaitViewPresentation<InGameUnitDetailViewController>(cancellationToken);
                var inGameUnitDetailViewController = uiViewController as InGameUnitDetailViewController;
                
                HideLongTapIndicator();
                longPressButton1.enabled = false;
                
                await CustomDelay(cancellationToken, 1500);

                HideGrayOut();
                await FadeInOverlayGrayOut(cancellationToken);
                await ShowTutorialText(cancellationToken, "長押しをやめると詳細情報が閉じるよ", 0);
                if (inGameUnitDetailViewController != null)
                {
                    // trueフラグはInGamePresenterで表示時に設定
                    inGameUnitDetailViewController.IsPlayingTutorial = false;
                    inGameUnitDetailViewController.Close();
                }
                await HideTutorialText(cancellationToken);
                
                HideOverlayGrayOut();
                await ResumeInGame(cancellationToken);
                
                // ユニット詳細チュートリアルのフラグを変更する
                SetPlayingUnitDetailTutorial(false);
                
                await CustomDelay(cancellationToken, 1200);

                await PauseInGame(cancellationToken);
                await FadeInGrayOut(cancellationToken);
                
                await ShowTutorialText(cancellationToken, "「キャラ」がファントムゲートを\n壊せばクリアだよ！", 0);
                await ShowTutorialText(cancellationToken, "ここからは自由に戦ってみよう！", 0);
                await HideTutorialText(cancellationToken);
                
                // クリアまでフリー操作
                button1.enabled = true;
                button2.enabled = true;
                button3.enabled = true;
                button4.enabled = true;
                button5.enabled = true;
                
                longPressButton1.enabled = true;
                longPressButton2.enabled = true;
                longPressButton3.enabled = true;
                longPressButton4.enabled = true;
                longPressButton5.enabled = true;
                
                // 再開して、画面を明るくする
                await FadeOutGrayOut(cancellationToken);
                await ResumeInGame(cancellationToken);
                
                // InGamePresenterで導入パートが完了しているか確認
                await UniTask.WaitUntil(IsEndTutorial, cancellationToken: cancellationToken);
                
                await ShowTutorialText(cancellationToken, "ありがとう！\n「ファントム」の侵攻を防げたよ！", 0);
                await ShowTutorialText(cancellationToken, "今回助けてくれたキャラたちは、\nそれぞれの作品の世界を救うために\n帰らないといけないんだ…！", 0);
                await ShowTutorialText(cancellationToken, "まずは君の力になってくれる\n「キャラ」を仲間にしよう！", 0);
                await HideTutorialText(cancellationToken);
                
                // スキップボタンを消す
                HideSkipButton();
            }
            catch (OperationCanceledException)
            {
                if (!_isInternalCancellation) throw;
                
                // 内部からのキャンセル（スキップボタン）の場合のみスキップ処理
                HideSkipButton();
                SkipTutorial();
            }
            finally
            {
                _cancellationTokenSource?.Dispose();
                _cancellationTokenSource = null;
                _isInternalCancellation = false;
            }
            
            // ダウンロード未完了の場合 ダウンロード待機表示 
            var afterTutorialCancellationToken = TutorialViewController.ActualView.destroyCancellationToken;
            
            // スキップをした場合はここでステージクリア処理を行う
            await SkipIntroductionTutorialUseCase.SkipIntroductionTutorial(afterTutorialCancellationToken);
            
            // ダウンロードを開始していない場合ダウンロードをする
            await DoTutorialDownloadIfNeeds(afterTutorialCancellationToken);
            
            // ダウンロード完了まで待つ
            await AwaitTutorialDownload(afterTutorialCancellationToken);
            
            SetPlayingTutorial(false);

            // ローカル通知の更新
            LocalNotificationScheduler.RefreshTutorialSchedule();

            // ホームへ遷移
            TransitionToHome();
            // チュートリアル終了
        }
        
        
        async UniTask DoTutorialDownloadIfNeeds(CancellationToken cancellationToken)
        {
            if (!TutorialAssetDownloader.IsStartBackgroundDownload())
            {
                TutorialViewController.ActualView.Hidden = true;

                // ダウンロードダイアログ表示のためチュートリアル画面を非表示にする
                var completionSource = new UniTaskCompletionSource();
                TutorialAssetDownloader.DoBackgroundAssetDownload(
                    () => completionSource.TrySetResult(),
                    () => ApplicationRebootor.Reboot(),
                    TutorialBackGroundDownloadUseCase.GetGameVersion());

                // ダウンロードを開始するまで待機
                await completionSource.Task.AttachExternalCancellation(cancellationToken);


                // ダウンロードが開始された場合はダウンロード表示をする ダウンロード済みの場合表示しない
                if (TutorialAssetDownloader.IsStartBackgroundDownload())
                {
                    TutorialViewController.ActualView.Hidden = false;
                    DisplayTutorialDownloadIfNeeds();
                }
            }
        }

        async UniTask AwaitTutorialDownload(CancellationToken cancellationToken)
        {
            if (_downloadProgress < 1)  
            {
                // 画面遷移演出
                TutorialViewController.ActualView.Hidden = false;
                await TutorialViewController.PlayAppearTutorialDownloadTransition(cancellationToken);
                // ダウンロード待機画面表示
                TutorialViewController.ShowTutorialDownloadScreen();

                await TutorialViewController.PlayDisappearTutorialDownloadTransition(cancellationToken);

                // ダウンロード完了で終了
                await UniTask.WaitUntil(() => _downloadProgress >= 1, cancellationToken: cancellationToken);
            }
        }
        
        void ShowSkipButton()
        {
            TutorialViewController.ShowSkipButton(OnSkipButtonTapped);
        }
        
        void HideSkipButton()
        {
            TutorialViewController.HideSkipButton();
        }
        
        void OnSkipButtonTapped()
        {
            // ポーズ中ではない場合、ポーズしてダイアログ表示する
            if (!_isPause) base.PauseInGame();
            TutorialViewController.ActualView.Hidden = true;
            _isDisplayAnyDialog = true;
            
            MessageViewUtil.ShowMessageWith2Buttons(
                "確認",
                "チュートリアルバトルを\nスキップしますか？\n\n※再挑戦はできません。\n" +
                "データダウンロードが完了していない場合、\nダウンロード待ちになります。",
                String.Empty,
                "スキップする",
                "キャンセル",
                () =>
                {
                    _isDisplayAnyDialog = false;
                    _isInternalCancellation = true;
                    _cancellationTokenSource?.Cancel();
                    _cancellationTokenSource?.Dispose();
                    _cancellationTokenSource = null;
                    TutorialViewController.HideSkipButton();
                    TutorialViewController.HideTutorialCanvass();
                    TutorialViewController.ActualView.Hidden = false;
                },
                CancelSkipTutorialDialog,
                CancelSkipTutorialDialog);
        }
        
        void CancelSkipTutorialDialog()
        {
            // 連打防止
            if (!_isDisplayAnyDialog) return;
                    
            TutorialViewController.ActualView.Hidden = false;
            if (!_isPause) base.ResumeInGame();
            _isDisplayAnyDialog = false;
        }
        
        void SetupTutorialDownloadIfNeeds()
        {
            TutorialAssetDownloader.SetProgressUpdateAction(SetDownloadProgress);
            TutorialAssetDownloader.SetPresentUserApproval(this);
        }

        void DisplayTutorialDownloadIfNeeds()
        {
            if (!TutorialAssetDownloader.IsStartBackgroundDownload()) return;
            
            TutorialViewController.ShowCircleGaugeProgress();
            _downloadProgress = TutorialAssetDownloader.GetDownloadProgress();
            SetDownloadProgress(_downloadProgress);
            
        }

        async UniTask PauseInGame(CancellationToken token)
        {
            // スキップダイアログが出ている間は待機
            await UniTask.WaitUntil(() => !_isDisplayAnyDialog, cancellationToken: token);
            
            _isPause = true;
            base.PauseInGame();
        }

        async UniTask ResumeInGame(CancellationToken token)
        {
            // スキップダイアログが出ている間は待機
            await UniTask.WaitUntil(() => !_isDisplayAnyDialog, cancellationToken: token);
            
            _isPause = false;
            base.ResumeInGame();
        }

        async UniTask CustomDelay(CancellationToken cancellationToken, float duration)
        {
            var elapsedTime = 0f;
            while (elapsedTime < duration)
            {
                // スキップメッセージ表示中はカウントしない
                if (_isDisplayAnyDialog)
                {
                    await UniTask.Yield(PlayerLoopTiming.Update, cancellationToken);
                    continue;
                }

                elapsedTime += Time.deltaTime * 1000;
                await UniTask.Yield(PlayerLoopTiming.Update, cancellationToken);
            }
        }
        
        void SetDownloadProgress(DownloadProgress progress)
        {
            _downloadProgress = progress;
            // チュートリアル中は右下のゲージのみ更新
            TutorialViewController.SetCircleGaugeProgress(progress);
            
            if (progress >= 1)
            {
                TutorialViewController.ShowCircleGaugeCompletedText();
            } 
        }
        
        async UniTask<bool> ITutorialAssetDownloadPresentUserApproval.PresentUserWithAssetBundleDownloadScreenAndCheckResult(
            CancellationToken cancellationToken,
            AssetDownloadSize downloadSize,
            FreeSpaceSize freeSpaceSize)
        {
            // NOTE: アセットダウンロード確認画面を表示する
            var completionSource = new UniTaskCompletionSource<bool>();
            var controller = ViewFactory.Create<
                AssetDownloadNoticeViewController, 
                AssetDownloadNoticeViewController.Argument>(
                new AssetDownloadNoticeViewController.Argument(
                    DownloadSize: downloadSize,
                    Download: () => completionSource.TrySetResult(true),
                    Cancel: () => completionSource.TrySetResult(false)));
            TutorialViewController.PresentModally(controller);

            // ダイアログ操作待ち
            await using var _ =
                cancellationToken.Register(
                    () => completionSource.TrySetCanceled(),
                    useSynchronizationContext: true);
            return await completionSource.Task;
        }

        async UniTask ITutorialAssetDownloadPresentUserApproval.PresentUserWithFreeSpaceError(
            CancellationToken cancellationToken)
        {
            // チュートリアル画面を非表示にする
            _isDisplayAnyDialog = true;
            var isTutorialViewHidden = TutorialViewController.ActualView.Hidden;
            TutorialViewController.ActualView.Hidden = true;
            
            var completionSource = new UniTaskCompletionSource<bool>();

            MessageViewUtil.ShowMessageWithOk(
                "容量不足エラー",
                "ダウンロードに必要な容量が不足しています。",
                String.Empty,
                () =>
                {
                    completionSource.TrySetResult(true);
                    TutorialViewController.ActualView.Hidden = isTutorialViewHidden;
                },
                enableEscape: false);

            // ダイアログ操作待ち
            await using var _ =
                cancellationToken.Register(
                    () => completionSource.TrySetCanceled(),
                    useSynchronizationContext: true);
            await completionSource.Task;
        }

        async UniTask<bool> ITutorialAssetDownloadPresentUserApproval.PresentUserWithAssetBundleRetryableDownload(
            CancellationToken cancellationToken)
        {
            // チュートリアル画面を非表示にする
            _isDisplayAnyDialog = true;
            var isTutorialViewHidden = TutorialViewController.ActualView.Hidden;
            TutorialViewController.ActualView.Hidden = true;

            // チュートリアルをポーズする
            base.PauseInGame();
            
            var completionSource = new UniTaskCompletionSource<bool>();
            MessageViewUtil.ShowConfirmMessage(
                title: Terms.Get("login_progress_message_asset_download_other_title"),
                message: Terms.Get("login_progress_message_asset_download_other_message"),
                attentionMessage: string.Empty,
                onOk: () =>
                {
                    _isDisplayAnyDialog = false;
                    completionSource.TrySetResult(true);
                    TutorialViewController.ActualView.Hidden = isTutorialViewHidden;

                    // チュートリアルを再開する
                    base.ResumeInGame();
                },
                onCancel: () => completionSource.TrySetResult(false),
                enableEscape: false);

            // ダイアログ操作待ち
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());
            return await completionSource.Task;
        }

        bool IsTargetEnemy(TutorialIndicatorTarget target)
        {
            var targetId = target.GetComponent<FieldUnitConditionComponent>().CharacterId;
            return targetId == new MasterDataId("e_glo_00001_tutorial_Normal_Blue");
        }
    }
}
