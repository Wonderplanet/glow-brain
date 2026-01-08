using System;
using System.IO;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Resolvers;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Scenes.Login.Domain.UseCase;
using UnityEngine.ResourceManagement.Exceptions;
using WonderPlanet.ResourceManagement;
using WonderPlanet.StorageSupporter;
using WonderPlanet.StorageSupporter.Utils;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Exceptions;
using WPFramework.Exceptions.Mappers;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.AssetDownloader
{

    public class TutorialAssetDownloader : ITutorialAssetDownloader
    {
        [Inject] IAssetManagement AssetManagement { get; }
        [Inject] IOExceptionMapper IOExceptionMapper { get; }
        [Inject] IAssetCdnHostResolver AssetCdnHostResolver { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }

        // NOTE: 最初にダウンロードするラベル
        const string FastFollowAssetKey = "fastfollow";
        bool _isStartBackgroundDownload;
        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();
        float _progress;
        Action<DownloadProgress> _updateProgressAction;
        ITutorialAssetDownloadPresentUserApproval _presentUserApproval;

        public void DoBackgroundAssetDownload(
            Action onAssetDownloadConfirmed,
            Action onAssetDownloadRefused,
            GameVersionModel gameVersionModel)
        {
            ApplicationLog.Log(nameof(TutorialAssetDownloader), $"バックグラウンドダウンロード開始");
            // 再度行われないようにする
            if (_isStartBackgroundDownload)
            {
                return;
            }

            _isStartBackgroundDownload = true;
            _cancellationTokenSource = new CancellationTokenSource();

            DoAsync.Invoke(_cancellationTokenSource.Token, async cancellationToken =>
            {
                try
                {
                    // NOTE: コンテンツカタログを更新する
                    await UpdateMainContentCatalog(cancellationToken, gameVersionModel);

                    // NOTE: ダウンロードサイズ、空き容量を取得する
                    var needsDownload =
                        await TaskRunner.Retryable(cancellationToken, async (ct, count) =>
                        {
                            // NOTE: ダウンロードサイズ、空き容量を取得する
                            var downloadMetrics = GetDownloadMetrics();

                            // NOTE: ダウンロード対象がない（サイズが０）なら抜ける
                            if (downloadMetrics.downloadSize.IsZero())
                            {
                                _progress = 1.0f;
                                return false;
                            }

                            // NOTE: アセットバンドルのマニュフェストファイルを取得し差分があるかを確認する
                            //       差分がある場合アセットのダウンロードを行う
                            if (!await _presentUserApproval.PresentUserWithAssetBundleDownloadScreenAndCheckResult(ct,
                                    downloadMetrics.downloadSize, downloadMetrics.freeSpaceSize))
                            {
                                // NOTE: 「後で」または「キャンセル」を選択した場合は、ダウンロードしないが容量チェックは行う
                                await CheckFreeSpaceAndShowErrorIfNeeded(ct, downloadMetrics.downloadSize, downloadMetrics.freeSpaceSize);
                                
                                _isStartBackgroundDownload = false;
                                throw new TutorialAssetBundleDownloadPermissionRefusedException();
                            }

                            // 空き容量チェック
                            await CheckFreeSpaceAndShowErrorIfNeeded(ct, downloadMetrics.downloadSize, downloadMetrics.freeSpaceSize);

                            // ダウンロードが開始されたので進める
                            onAssetDownloadConfirmed?.Invoke();

                            return true;
                        });

                    var progress = new Progress<float>(progress =>
                    {
                        // 進捗の更新
                        _progress = progress;
                        _updateProgressAction?.Invoke(new DownloadProgress(progress));
                    });
                    
                    var assetBundleProgressReporter = new ProgressReporter(progress);
                    var progressReporter = assetBundleProgressReporter.Create();

                    // NOTE: ダウンロードが必要ない
                    if (!needsDownload)
                    {
                        progressReporter.Report(1.0f);
                        ApplicationLog.Log(nameof(TutorialAssetDownloader), $"ダウンロード済み");

                        // ダウンロード済みのため遷移する
                        onAssetDownloadConfirmed?.Invoke();
                        return;
                    }

                    var downloadProgress =
                        new Progress<ProgressData>(progressData => progressReporter.Report(progressData.Progress));

                    try
                    {
                        // アセットはバックグラウンドでダウンロードする
                        await AssetManagement.DownloadAssetDependencies(cancellationToken, FastFollowAssetKey,
                            downloadProgress);
                    }
                    catch (CustomAssetBundleNetworkException)
                    {
                        // NOTE: より詳細のエラーを追う場合はMessageを利用して以下のページを参考に対応を行う
                        //       https://docs.unity3d.com/Packages/com.unity.addressables@1.20/manual/LoadingAssetBundles.html

                        if (await _presentUserApproval.PresentUserWithAssetBundleRetryableDownload(cancellationToken))
                        {
                            // NOTE: タスクのリトライを行うためにエラーを発生させる
                            throw new TaskRetryableRequestedException();
                        }

                        throw new AssetBundleDownloadFailedException();
                    }

                    progressReporter.Report(1.0f);
                }
                catch (TutorialAssetBundleDownloadPermissionRefusedException)
                {
                    // NOTE: ダウンロードを「あとで」または「キャンセル」した場合
                    onAssetDownloadRefused?.Invoke();
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
                catch (IOException ioe)
                {
                    throw IOExceptionMapper.Map(ioe);
                }

                ApplicationLog.Log(nameof(TutorialAssetDownloader), $"バックグラウンドダウンロード終了");
            });
        }

        public void CancelBackgroundAssetDownload()
        {
            _isStartBackgroundDownload = false;
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
        }

        async UniTask UpdateMainContentCatalog(CancellationToken cancellationToken, GameVersionModel gameVersionModel)
        {
            // NOTE: GameVersionModelの情報を利用しカタログのURLを構築する
            var relativePath = gameVersionModel.AssetCatalogDataPath;
            var hostAndRootPath = new UriBuilder(AssetCdnHostResolver.Resolve().Uri)
            {
                Path = Path.GetDirectoryName(relativePath) ?? string.Empty
            };
            // NOTE: カタログの更新を行う際に、設置場所のパスとファイル名を別々に渡す必要があるため情報を分割する
            var catalogFileName = Path.GetFileName(gameVersionModel.AssetCatalogDataPath);

            ApplicationLog.Log(nameof(LoginUseCases), $"{hostAndRootPath}/{catalogFileName}のカタログを確認");

#if UNITY_EDITOR
            // NOTE: Local Hostedの場合はエディタ内のカタログを利用して欲しいため処理をスキップする
            const string localHostedProfileName = "Local Hosted";
            var settings = UnityEditor.AddressableAssets.AddressableAssetSettingsDefaultObject.GetSettings(true);
            if(settings.activeProfileId == settings.profileSettings.GetProfileId(localHostedProfileName))
            {
                ApplicationLog.Log(nameof(LoginUseCases), $"{localHostedProfileName}のためカタログの更新をスキップします");
                return;
            }
#endif // UNITY_EDITOR

            var catalogLocation =
                new CatalogAndContentLocation(hostAndRootPath.ToString(), catalogFileName);
            // NOTE: コンテンツカタログのダウンロードを実行する
            await TaskRunner.Retryable(cancellationToken, async (ct, count) =>
            {
                try
                {
                    await AssetManagement.UpdateMainContentCatalog(cancellationToken, catalogLocation);
                }
                catch (OperationException)
                {
                    if (await _presentUserApproval.PresentUserWithAssetBundleRetryableDownload(ct))
                    {
                        // NOTE: タスクのリトライを行うためにエラーを発生させる
                        throw new TaskRetryableRequestedException();
                    }

                    // NOTE: エディタの場合UnityLocalizationのEditorからの設定ファイルの参照に失敗してしまうためプロセス自体を再度起動させる必要がある
                    throw new AssetBundleContentCatalogUpdateFailedException();
                }
            });
        }

        (AssetDownloadSize downloadSize, FreeSpaceSize freeSpaceSize) GetDownloadMetrics()
        {
            var downloadSize = new AssetDownloadSize((ulong)AssetManagement.GetAssetDownloadSize(FastFollowAssetKey));
            var freeSpaceSize = new FreeSpaceSize(StorageSupport.GetAvailableFreeSpace());

            ApplicationLog.Log(nameof(LoginUseCases), $"ディスク空き容量 {DataSizeConverter.ConvertToString(freeSpaceSize.Value)}");
            ApplicationLog.Log(nameof(LoginUseCases), $"ダウンロードサイズ {DataSizeConverter.ConvertToString(downloadSize.Value)}");

            // NOTE: アセットバンドルのマニュフェストファイルを取得し差分があるかを確認する
            //       差分がある場合アセットのダウンロードを行う
            return (downloadSize, freeSpaceSize);
        }
        
        public DownloadProgress GetDownloadProgress()
        {
            return new DownloadProgress(_progress);
        }
        
        public void SetProgressUpdateAction(Action<DownloadProgress> updateProgressAction)
        {
            _updateProgressAction = updateProgressAction;
        }

        public void SetPresentUserApproval(ITutorialAssetDownloadPresentUserApproval presentUserApproval)
        {
            _presentUserApproval = presentUserApproval;
        }
        
        public bool IsStartBackgroundDownload()
        {
            return _isStartBackgroundDownload;
        }

        async UniTask CheckFreeSpaceAndShowErrorIfNeeded(CancellationToken ct, AssetDownloadSize downloadSize, FreeSpaceSize freeSpaceSize)
        {
            // 空き容量チェック
            if (downloadSize > freeSpaceSize)
            {
                // 空き容量エラーダイアログ
                await _presentUserApproval.PresentUserWithFreeSpaceError(ct);

                // NOTE: タスクのリトライを行うためにエラーを発生させる
                throw new TaskRetryableRequestedException();
            }
        }
    }
}
