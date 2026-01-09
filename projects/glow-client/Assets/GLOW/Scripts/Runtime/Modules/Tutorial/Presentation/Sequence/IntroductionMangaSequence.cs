using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.Tutorial.Domain.AssetDownloader;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Scenes.AssetDownloadNotice.Presentation.Views;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    /// <summary>
    /// 導入パートチュートリアル
    /// 導入チュートリアル前の導入漫画演出
    /// </summary>
    public class IntroductionMangaSequence : BaseInGameTutorialSequence, ITutorialAssetDownloadPresentUserApproval
    {
        [Inject] ITutorialAssetDownloader TutorialAssetDownloader { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] IViewFactory ViewFactory { get; }

        CancellationTokenSource _cancellationTokenSource;
        DownloadProgress _downloadProgress;
        
        public override async UniTask Play(CancellationToken token)
        {
            _cancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(token);
            var cancellationToken = _cancellationTokenSource.Token;

            // ダウンロードをしている場合はダウンロード表示設定
            if (TutorialAssetDownloader.IsStartBackgroundDownload())
            {
                TutorialViewController.ShowCircleGaugeProgress();
                _downloadProgress = TutorialAssetDownloader.GetDownloadProgress();
                SetDownloadProgress(_downloadProgress);
                TutorialAssetDownloader.SetProgressUpdateAction(SetDownloadProgress);
                TutorialAssetDownloader.SetPresentUserApproval(this);
            }
        
            // 導入漫画表示
            var manager = GetTutorialIntroductionMangaManager();
            await TutorialViewController.PlayTutorialManga(cancellationToken, manager);
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
            var controller = ViewFactory.Create<AssetDownloadNoticeViewController, AssetDownloadNoticeViewController.Argument>(
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

        async UniTask ITutorialAssetDownloadPresentUserApproval.PresentUserWithFreeSpaceError(CancellationToken cancellationToken)
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
            await completionSource.Task;
        }

        async UniTask<bool> ITutorialAssetDownloadPresentUserApproval.PresentUserWithAssetBundleRetryableDownload(CancellationToken cancellationToken)
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
    }
}