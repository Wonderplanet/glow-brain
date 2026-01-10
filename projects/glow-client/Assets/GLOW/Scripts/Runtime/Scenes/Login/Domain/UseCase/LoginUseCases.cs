using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants;
using UnityHTTPLibrary;
using WonderPlanet.AnalyticsBridge;
using WonderPlanet.CultureSupporter.Time;
using WonderPlanet.ResourceManagement;
using WonderPlanet.StorageSupporter;
using WondlerPlanet.LocalNotification;
using WPFramework.Constants.Zenject;
using WPFramework.Exceptions;
using WPFramework.Modules.Benchmark;
using WPFramework.Modules.Log;
using GLOW.Core.Constants.Benchmark;
using GLOW.Core.Constants.ObservabilityKit;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.MasterData;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.ComebackDailyBonus;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Modules;
using GLOW.Core.Domain.Modules.Network;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Resolvers;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.Login.Domain.Constants.Login;
using GLOW.Scenes.Login.Domain.UseCases;
using GLOW.Scenes.Title.Domains.Definition.Service;
using UnityEngine.ResourceManagement.Exceptions;
using WonderPlanet.AnalyticsBridge.Adjust;
using WonderPlanet.CrashReporterBridge;
using WonderPlanet.ObservabilityKit;
using WonderPlanet.RemoteNotificationBridge;
using WonderPlanet.StorageSupporter.Utils;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Constants.MasterData;
using WPFramework.Domain.Modules;
using WPFramework.Domain.Repositories;
using WPFramework.Domain.Services;
using WPFramework.Exceptions.Mappers;
using Zenject;
using IGameManagement = GLOW.Core.Domain.Repositories.IGameManagement;
using ITimeMeasurement = WonderPlanet.ObservabilityKit.ITimeMeasurement;

namespace GLOW.Scenes.Login.Domain.UseCase
{
    public sealed class LoginUseCases
    {
        // NOTE: 最初にダウンロードするラベル
        const string FastFollowAssetKey = "fastfollow";

        [Inject] ILoginPresentUserApproval LoginPresentUserApproval { get; }
        [Inject] ILoginPhaseNotifier LoginPhaseNotifier { get; }
        [Inject] IOverrideAuthenticateTokenService AuthenticateService { get; }
        [Inject] IGameApiRequestHeaderAssignor GameApiRequestHeaderAssignor { get; }
        [Inject] IAgreementRequestHeaderAssignor AgreementRequestHeaderAssignor { get; }
        [Inject] IMasterDataManagement MasterDataManagement { get; }
        [Inject] IAssetManagement AssetManagement { get; }
        [Inject] IGameService GameService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] ICalibrationDateTimeOffsetSource CalibrationDateTimeOffsetSource { get; }
        [Inject] ICalibrationDateTimeSource CalibrationDateTimeSource { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Game)] ServerApi GameApiContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Agreement)] ServerApi AgreementApiContext { get; }
        [Inject] IOExceptionMapper IOExceptionMapper { get; }
        [Inject] ILocalNotificationCenter LocalNotificationCenter { get; }
        [Inject] AnalyticsCenter AnalyticsCenter { get; }
        [Inject] CrashReportCenter CrashReportCenter { get; }
        [Inject] RemoteNotificationCenter RemoteNotificationCenter { get; }
        [Inject] IMstDataService MstDataService { get; }
        [Inject] TimeMeasurementContainer TimeMeasurementContainer { get; }
        [Inject] IAssetCdnHostResolver AssetCdnHostResolver { get; }
        [Inject] ISessionResumeApproval SessionResumeApproval { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository {get;}
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IMstPartyUnitCountDataRepository MstPartyUnitCountDataRepository { get; }
        [Inject] ILoginTrackingTransparencyApproval LoginTrackingTransparencyApproval { get; }
        [Inject] IStoreCoreModule StoreCoreModule { get; }
        [Inject] IEnvironmentResolver EnvironmentResolver { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IAgreementService AgreementService { get; }
        [Inject] IAcquisitionDisplayedUnitIdsRepository AcquisitionDisplayedUnitIdsRepository { get; }
        [Inject] IReceivedDailyBonusRepository ReceivedDailyBonusRepository { get; }
        [Inject] IReceivedEventDailyBonusRepository ReceivedEventDailyBonusRepository { get; }
        [Inject] IReceivedComebackDailyBonusRepository ReceivedComebackDailyBonusRepository { get; }

        public async UniTask Login(CancellationToken cancellationToken, IProgress<float> progress)
        {
            // NOTE: ログイン時間の計測を行う
            ITimeMeasurement measurement = new WonderPlanet.ObservabilityKit.TimeMeasurement(TimeBenchmark.Name.OutGame);
            measurement.Start();

            var progressReporter = new ProgressReporter(progress);
            var timeCalibrationProgress = progressReporter.Create();
            var authenticateProgress = progressReporter.Create();
            var sdkInitializeProgress = progressReporter.Create();
            var downloadMstDataProgress = progressReporter.Create();
            var loadMasterDataProgress = progressReporter.Create();
            var fetchUseDataProgress = progressReporter.Create();
            var licenseAgreementProgress = progressReporter.Create();
            var fetchGameVersionProgress = progressReporter.Create();

            var assetBundleProgressReporter = new ProgressReporter(progress);
            var downloadAssetBundleProgress = assetBundleProgressReporter.Create();

            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.None));

            await Authenticate(cancellationToken, authenticateProgress, measurement);
            
            await FetchServerTime(cancellationToken, timeCalibrationProgress, measurement);
            await FetchGameVersion(cancellationToken, fetchGameVersionProgress, measurement);
                
            // NOTE: GameVersionModelを使うときはFetchGameVersionより後ろで叩くこと
            GameVersionModel gameVersionModel = GameRepository.GetGameVersion();

            var downloadAndLoadMstTypes = new[]
            {
                MasterType.Mst,
                MasterType.Opr,
                MasterType.MstI18n,
                MasterType.OprI18n
            };

            var updateAndFetchResultModel = await UpdateAndFetch(cancellationToken, fetchUseDataProgress);

            await LicenseAgreement(cancellationToken, gameVersionModel, licenseAgreementProgress, measurement);

            await SDKInitialize(cancellationToken, sdkInitializeProgress, measurement, updateAndFetchResultModel.FetchOtherModel.UserProfileModel);

            await DownloadMstDataAll(cancellationToken, downloadAndLoadMstTypes, gameVersionModel, downloadMstDataProgress);

            await LoadMstDataAll(cancellationToken, downloadAndLoadMstTypes, gameVersionModel, loadMasterDataProgress);

            ApplyUpdateAndFetchResult(updateAndFetchResultModel);

            // チュートリアル 導入パート完了前はここでダウロードしない
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            if (tutorialStatus != TutorialSequenceIdDefinitions.TutorialStart &&
                tutorialStatus != TutorialSequenceIdDefinitions.TutorialStartIntroduction)
            {
                // NOTE: アセットバンドルのダウンロードを0%から再度カウント
                await DownloadAssetBundle(cancellationToken, gameVersionModel, downloadAssetBundleProgress, measurement);
            }

            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.Complete));

            // NOTE: この段階まででログイン処理が完了しているので計測を終了する
            measurement.Report();
            TimeMeasurementContainer.RemoveTimeMeasurement(TimeBenchmark.Name.OutGame);
        }

        public async UniTask CheckCurrentPlaySession(CancellationToken cancellationToken)
        {
            // 導入チュートリアル中は中断復帰から再開しない
            if (GameRepository.GetGameFetchOther().TutorialStatus.IsIntroduction()) return;

            // NOTE: 中断復帰
            var sessionModel = GameRepository.GetGameFetchOther().UserInGameStatusModel;
            if (sessionModel.IsStartedSession)
            {
                await SessionResumeApproval.ShowResumeSessionConfirmView(
                    cancellationToken,
                    sessionModel.InGameContentType,
                    sessionModel.TargetMstId);
            }
        }

        public void ChangeTransitToHomePhase()
        {
            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.TransitionToHome));
        }

        async UniTask LicenseAgreement(
            CancellationToken cancellationToken,
            GameVersionModel gameVersionModel,
            IProgress<float> progress,
            ITimeMeasurement measurement)
        {
            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.LicenseAgreement));

            ApplicationLog.Log(nameof(LoginUseCases), nameof(LicenseAgreement));

            AgreementRequestHeaderAssignor.SetRequestHeaders(AgreementApiContext);

            // 利用規約同意 完了判定
            bool isAgreementConsented = gameVersionModel.IsAgreementConsented();

            // 同意モジュール 完了判定
            bool isAgreementModuleEnd = true;
            var myId = GameRepository.GetGameFetchOther().UserProfileModel.MyId;
            try
            {
                await AgreementService.GetConsentInfos(cancellationToken, myId);
            }
            catch (ServerErrorException se)
            {
                // NotFoundは同意モジュールが未同意のためエラーを出力しない
                if (se.StatusCode == HTTPStatusCodes.NotFound)
                {
                    isAgreementModuleEnd = false;
                }
                else
                {
                    ApplicationLog.LogError(nameof(LoginUseCases), $"GetConsentInfos ServerErrorException: {se.StatusCode}");
                }
            }
            catch (InternetNotReachableException)
            {
                throw;  // タイトルへ戻る処理のために再スロー
            }
            catch (NetworkTimeoutException)
            {
                throw;  // タイトルへ戻る処理のために再スロー
            }
            catch (Exception e)
            {
                // 通信エラーのExceptionなら通常のエラーハンドリングに任せて通信エラーメッセージ表示
                if (NetworkErrorExceptionMessage.IsNetworkErrorExceptionMessage(e.Message)) throw;
                
                ApplicationLog.LogError(nameof(LoginUseCases), $"GetConsentInfos Exception: {e.Message}");
            }

            // どちらも完了していたら終了
            if (isAgreementConsented && isAgreementModuleEnd)
            {
                ApplicationLog.Log(nameof(LoginUseCases), $"利用許諾バージョン{gameVersionModel.TosVersion}は同意済みです");
                progress.Report(1.0f);
                return;
            }

            // 各種規約同意開始
            measurement.Stop();

            // 利用規約同意
            if (!isAgreementConsented)
            {
                var agreement = await LoginPresentUserApproval
                    .PresentUserWithAgreementScreenAndCheckResult(cancellationToken, gameVersionModel);
                if (!agreement)
                {
                    throw new LicenseAgreementPermissionRefusedException();
                }
            }

            // 同意モジュール
            if (!isAgreementModuleEnd)
            {
                try
                {
                    var result = await AgreementService.GetConsentRequestUrl(
                        cancellationToken,
                        myId,
                        Language.ja,
                        new AgreementCallbackUrl(Credentials.AgreementRedirectURL),
                        AgreementBnLogoDisplayFlag.True,
                        new AgreementConsentType[]
                        {
                            AgreementConsentType.Type0,
                            AgreementConsentType.Type1,
                            AgreementConsentType.Type2,
                            AgreementConsentType.Type3
                        });

                    await LoginPresentUserApproval
                        .PresentUserWithAgreementModuleScreenAndCheckResult(cancellationToken, result.Url);
                }
                catch (InternetNotReachableException)
                {
                    throw;  // タイトルへ戻る処理のために再スロー
                }
                catch (NetworkTimeoutException)
                {
                    throw;  // タイトルへ戻る処理のために再スロー
                }
                catch (Exception e)
                {
                    // 通信エラーのExceptionなら通常のエラーハンドリングに任せて通信エラーメッセージ表示
                    if (NetworkErrorExceptionMessage.IsNetworkErrorExceptionMessage(e.Message)) throw;
                    
                    ApplicationLog.LogError(nameof(LoginUseCases), $"AgreementRedirectURL Error: {e.Message}");
                }
            }

            measurement.Start();

            progress.Report(1.0f);
        }

        async UniTask FetchServerTime(
            CancellationToken cancellationToken,
            IProgress<float> progress,
            ITimeMeasurement measurement)
        {
            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.FetchServerTime));

            // NOTE: サーバーから取得した時間をもとに時間校正を行う
            var gameServerTimeModel = await GameService.FetchServerTime(cancellationToken);
            CalibrationDateTimeOffsetSource.Calibration(gameServerTimeModel.ServerTime.ToUnixTimeMilliseconds());
            CalibrationDateTimeSource.Calibration(gameServerTimeModel.ServerTime.ToUnixTimeMilliseconds());

            var logMessage1 = ZString.Format(
                "現在時刻 {0}",
                WonderPlanet.CultureSupporter.Time.TimeProvider.DateTimeSource.Now.ToLocalTime());

            var logMessage2 = ZString.Format(
                "現在時刻 {0}",
                WonderPlanet.CultureSupporter.Time.TimeProvider.DateTimeOffsetSource.Now.ToLocalTime());

            ApplicationLog.Log(nameof(LoginUseCases), logMessage1);
            ApplicationLog.Log(nameof(LoginUseCases), logMessage2);

            progress.Report(1.0f);
        }

        async UniTask FetchGameVersion(
            CancellationToken cancellationToken,
            IProgress<float> progress,
            ITimeMeasurement measurement)
        {
            measurement.Start();

            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.FetchGameVersion));

            ApplicationLog.Log(nameof(LoginUseCases), nameof(FetchGameVersion));

            var gameVersionModel = await GameService.FetchVersion(cancellationToken);
            GameManagement.SaveGameVersion(gameVersionModel);

            // NOTE: GameAPIのヘッダーにリソースマスターデータのリリースキーとハッシュを追加する
            GameApiRequestHeaderAssignor.SetRequestHeaders(GameApiContext, gameVersionModel);

            progress.Report(1.0f);
        }

        async UniTask Authenticate(CancellationToken cancellationToken, IProgress<float> progress, ITimeMeasurement measurement)
        {
            measurement.Start();

            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.Authenticate));

            ApplicationLog.Log(nameof(LoginUseCases), nameof(Authenticate));

            var applicationSystemInfo = SystemInfoProvider.GetApplicationSystemInfo();

            var authenticateModel = await AuthenticateService.Authenticate(
                cancellationToken,
                applicationSystemInfo.DeviceUniqueIdentifier);

            GameApiContext.SessionStore = authenticateModel.SessionStore;

            progress.Report(1.0f);
        }

        async UniTask SDKInitialize(CancellationToken cancellationToken, IProgress<float> progress, ITimeMeasurement measurement, UserProfileModel userProfileModel)
        {
            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.SDKInitialize));

            ApplicationLog.Log(nameof(LoginUseCases), nameof(SDKInitialize));

            // NOTE: ATT確認があるため止める
            measurement.Stop();

            // NOTE: SDKの初期化処理を行う
            //       e.g FirebaseやAdjustなどのライセンス利用許諾以降にしか動かしてはいけないもの

            // ATTダイアログ表示前の確認ダイアログ
            var shouldRequestAuthorizationTracking = await AnalyticsCenter.ShouldRequestAuthorizationTracking(cancellationToken);
            if (shouldRequestAuthorizationTracking)
            {
                await LoginTrackingTransparencyApproval.ShowTrackingTransparencyConfirmView();

                // NOTE: Analytics関連の初期化処理を行う
                //       この段階で計測が開始される
                await AnalyticsCenter.RequestAuthorizationTracking(cancellationToken);
            }

            // NOTE: ATT確認の後から計測する
            measurement.Restart();

            AnalyticsCenter.SetUserId(userProfileModel.MyId.Value);
            AnalyticsCenter.Login();
            AnalyticsCenter.GetAgent<AdjustAgent>().AddSessionCallbackParameter(
                "app_user_id",
                userProfileModel.MyId.ToString());

            await AnalyticsCenter.StartAnalytics(cancellationToken);

            // NOTE: クラッシュレポートにはMyIDを利用する
            CrashReportCenter.SetUserId(userProfileModel.MyId.Value);

            // NOTE: Crashlyticsの初期化を行う
            await CrashReportCenter.StartReport(cancellationToken);

            // NOTE: プッシュ通知の場合確認が走る可能性があるため止める
            measurement.Stop();

            // NOTE: リモートプッシュの初期化を行う
            await RemoteNotificationCenter.Initialize(cancellationToken);
            await RemoteNotificationCenter.Start(cancellationToken);

            // NOTE: プッシュ通知の確認が完了したので再開する
            measurement.Restart();

            // NOTE: ローカルプッシュの初期化処理を行う
            await LocalNotificationCenter.Initialize().ToUniTask(cancellationToken: cancellationToken);
            var channelData = new NotificationChannelData()
            {
                Title = @"Title",
                ChannelId = @"ChannelId",
                Description = @"Description"
            };
            LocalNotificationCenter.RegisterChannel(channelData);

            // NOTE: Observability用にユーザー情報を設定する
            ObservabilityKit.SetUserMetaData(new UserMetaData(userProfileModel.MyId.Value));

            // NOTE: データ追跡権限の付与
            ObservabilityKit.AllowTrackingConsent();

            // NOTE: 広告基盤の初期化を行う
            // NOTE: SEED取り込みの際に併せて対応する
            // await InAppAdvertisingAgent.Initialize(cancellationToken);

            // NOTE: ObservabilityKitの監視を開始する
            ObservabilityKit.Begin();
            var environment = EnvironmentResolver.Resolve();
            // NOTE: 環境名を設定する
            ObservabilityKit.SetAttribute(ObservabilityAttribute.Common.EnvironmentName, environment.EnvironmentName);

            progress.Report(1.0f);
        }

        async UniTask DownloadMstDataAll(
            CancellationToken cancellationToken,
            IEnumerable<MasterType> targetMstTypes,
            GameVersionModel gameVersionModel,
            IProgress<float> progress)
        {
            // NOTE: ロード時間の計測を行う
            ITimeMeasurement measurement = new WonderPlanet.ObservabilityKit.TimeMeasurement(
                TimeBenchmark.Name.DownloadMasterData,
                ObservabilityKitLogLevel.Debug);

            measurement.Start();

            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.FetchMstDataManifest));

            ApplicationLog.Log(nameof(LoginUseCases), nameof(DownloadMstDataAll));

            var tasks = Enumerable
                .Select(targetMstTypes, type => DownloadMstData(cancellationToken, gameVersionModel, type))
                .ToList();
            await UniTask.WhenAll(tasks);

            measurement.Report();
            progress.Report(1.0f);
        }

        async UniTask DownloadMstData(
            CancellationToken cancellationToken,
            GameVersionModel gameVersionModel,
            MasterType masterType)
        {
            try
            {
                var path = gameVersionModel.GetMstPath(masterType);
                var hash = gameVersionModel.GetMstHash(masterType);

                // NOTE: パスまたはハッシュが空の場合はダウンロードを行わない
                if (string.IsNullOrEmpty(hash) || string.IsNullOrEmpty(path))
                {
                    return;
                }

                var mstFileName = MstDataPath.ParseFileNameFromPath(path);
                if (await MasterDataManagement.Validate(cancellationToken, masterType, mstFileName, hash))
                {
                    // NOTE: マスターデータのハッシュが一致した場合はダウンロードを行わない
                    return;
                }

                // NOTE: マスターデータのハッシュが一致しなかった場合はダウンロードを行う
                var mstData = await MstDataService.FetchMstData(cancellationToken, path);
                if (mstData == null || mstData.Length == 0)
                {
                    throw new MstDataDownloadSizeException();
                }

                // NOTE:「masterType」に合わせたディレクトリ以下に存在する古いマスターデータを削除する
                MasterDataManagement.DeleteAll(masterType);

                MasterDataManagement.Save(masterType, mstFileName, mstData);
            }
            catch (IOException ioe)
            {
                throw IOExceptionMapper.Map(ioe);
            }
        }

        async UniTask LoadMstDataAll(
            CancellationToken cancellationToken,
            IEnumerable<MasterType> targetMstTypes,
            GameVersionModel gameVersionModel,
            IProgress<float> progress)
        {
            // NOTE: ロード時間の計測を行う
            ITimeMeasurement measurement = new WonderPlanet.ObservabilityKit.TimeMeasurement(
                TimeBenchmark.Name.LoadMasterData,
                ObservabilityKitLogLevel.Debug);

            measurement.Start();

            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.LoadMstData));

            ApplicationLog.Log(nameof(LoginUseCases), nameof(LoadMstDataAll));

            var tasks = targetMstTypes
                .Select(type => LoadMstData(cancellationToken, gameVersionModel, type))
                .ToList();
            await UniTask.WhenAll(tasks);

            measurement.Report();

            progress.Report(1.0f);
        }

        void ApplyUpdateAndFetchResult(GameUpdateAndFetchResultModel updateAndFetchResultModel)
        {
            // MstDataのロード終了後に対応するもの

            // NOTE: ログインボーナスの受け取り情報を保存
            SaveDailyBonusInformation(
                updateAndFetchResultModel.FetchOtherModel.MissionReceivedDailyBonusModel,
                updateAndFetchResultModel.FetchOtherModel.MissionEventDailyBonusRewardModels,
                updateAndFetchResultModel.FetchOtherModel.UserMissionEventDailyBonusProgressModels,
                updateAndFetchResultModel.FetchOtherModel.ComebackBonusRewardModels,
                updateAndFetchResultModel.FetchOtherModel.UserComebackBonusProgressModels);

            // NOTE: パーティキャッシュ情報更新(このタイミングで行うのは中断復帰が存在するため)
            InitPartyCacheRepository(updateAndFetchResultModel.FetchOtherModel.UserPartyModels);

            // NOTE: ユーザーが所持しているユニットIDの一覧を取得
            InitReceivedUnitIdsRepository(updateAndFetchResultModel.FetchOtherModel.UserUnitModels);
        }

        async UniTask LoadMstData(CancellationToken cancellationToken, GameVersionModel gameVersionModel, MasterType masterType)
        {
            if (string.IsNullOrEmpty(gameVersionModel.GetMstHash(masterType)))
            {
                return;
            }

            try
            {
                await MasterDataManagement.Load(
                    cancellationToken,
                    masterType,
                    gameVersionModel.GetMstPath(masterType),
                    gameVersionModel.GetMstHash(masterType));
            }
            catch (Exception e)
            {
                ApplicationLog.LogError(
                    nameof(LoginUseCases),
                    ZString.Format("マスターデータのロードに失敗しました。MasterType:{0}, Error:{1}" , masterType, e.Message));

                // マスターデータのロードに失敗したら、該当するマスターデータを削除
                MasterDataManagement.DeleteAll(masterType);
                throw;
            }
        }

        async UniTask DownloadAssetBundle(
            CancellationToken cancellationToken,
            GameVersionModel gameVersionModel,
            IProgress<float> progress,
            ITimeMeasurement measurement)
        {
            // NOTE: ダウンロード時間は計測に含めない
            measurement.Stop();

            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.FetchAssetBundleManifest));

            ApplicationLog.Log(nameof(LoginUseCases), nameof(DownloadAssetBundle));

            try
            {
                // NOTE: コンテンツカタログを更新する
                await UpdateMainContentCatalog(cancellationToken, gameVersionModel);

                // NOTE: ダウンロードサイズ、空き容量を取得する
                var needsDownload =
                    await TaskRunner.Retryable(cancellationToken, async (ct, count) =>
                    {
                        // NOTE: ダウンロードサイズ、空き容量を取得する
                        var downloadMetricsUseCaseModel = GetDownloadMetricsUseCaseModel();

                        // NOTE: ダウンロード対象がない（サイズが０）なら抜ける
                        if (downloadMetricsUseCaseModel.TotalBytes.IsZero())
                        {
                            return false;
                        }

                        // NOTE: アセットバンドルのマニュフェストファイルを取得し差分があるかを確認する
                        //       差分がある場合アセットのダウンロードを行う
                        if (!await LoginPresentUserApproval.PresentUserWithAssetBundleDownloadScreenAndCheckResult(
                                ct,
                                downloadMetricsUseCaseModel))
                        {
                            throw new AssetBundleDownloadPermissionRefusedException();
                        }

                        // 空き容量チェック
                        if (downloadMetricsUseCaseModel.TotalBytes > downloadMetricsUseCaseModel.FreeSpaceBytes)
                        {
                            // 空き容量エラーダイアログ
                            await LoginPresentUserApproval.PresentUserWithFreeSpaceError(ct);

                            // NOTE: タスクのリトライを行うためにエラーを発生させる
                            throw new TaskRetryableRequestedException();
                        }

                        return true;
                    });

                // NOTE: ダウンロードが必要ない
                if (!needsDownload)
                {
                    progress.Report(1.0f);
                    return;
                }

                LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.FetchAssetBundle));

                var downloadProgress =
                    new Progress<ProgressData>(progressData =>
                    {
                        progress.Report(progressData.Progress);

                        // NOTE: ダウンロード済みサイズがトータルサイズと一致した場合はダウンロード完了
                        if (progressData.Downloaded >= progressData.Total)
                        {
                            LoginPhaseNotifier.LoginPhaseDetailEnded();
                            return;
                        }
                        // ダウンロード進捗表示情報
                        var label = new LoginPhaseDetailLabel(
                            progressData.Downloaded,
                            progressData.Total,
                            progressData.BytePerSec);

                        LoginPhaseNotifier.LoginPhaseDetailChanged(label);
                    });

                // NOTE: アセットバンドルのダウンロードを実行する
                await TaskRunner.Retryable(cancellationToken, async (ct, count) =>
                {
                    try
                    {
                        await AssetManagement.DownloadAssetDependencies(cancellationToken, FastFollowAssetKey, downloadProgress);
                    }
                    catch (CustomAssetBundleNetworkException)
                    {
                        // NOTE: より詳細のエラーを追う場合はMessageを利用して以下のページを参考に対応を行う
                        //       https://docs.unity3d.com/Packages/com.unity.addressables@1.20/manual/LoadingAssetBundles.html

                        if (await LoginPresentUserApproval.PresentUserWithAssetBundleRetryableDownload(ct))
                        {
                            // NOTE: タスクのリトライを行うためにエラーを発生させる
                            throw new TaskRetryableRequestedException();
                        }

                        throw new AssetBundleDownloadFailedException();
                    }
                });

                progress.Report(1.0f);
            }
            catch (IOException ioe)
            {
                throw IOExceptionMapper.Map(ioe);
            }
            finally
            {
                measurement.Restart();
            }
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
                    if (await LoginPresentUserApproval.PresentUserWithAssetBundleRetryableDownload(ct))
                    {
                        // NOTE: タスクのリトライを行うためにエラーを発生させる
                        throw new TaskRetryableRequestedException();
                    }

                    // NOTE: エディタの場合UnityLocalizationのEditorからの設定ファイルの参照に失敗してしまうため
                    // プロセス自体を再度起動させる必要がある
                    throw new AssetBundleContentCatalogUpdateFailedException();
                }
            });
        }

        async UniTask<GameUpdateAndFetchResultModel> UpdateAndFetch(CancellationToken cancellationToken, IProgress<float> progress)
        {
            // NOTE: ロード時間の計測を行う
            ITimeMeasurement measurement = new WonderPlanet.ObservabilityKit.TimeMeasurement(
                TimeBenchmark.Name.UpdateAndFetch,
                ObservabilityKitLogLevel.Debug);

            measurement.Start();

            LoginPhaseNotifier.LoginPhaseChanged(new LoginPhaseLabel(LoginPhases.FetchUserData));

            ApplicationLog.Log(nameof(LoginUseCases), nameof(UpdateAndFetch));

            // ログインボーナスの端末保存情報をロード
            ReceivedDailyBonusRepository.Load();
            ReceivedEventDailyBonusRepository.Load();
            ReceivedComebackDailyBonusRepository.Load();

            var updateAndFetchResultModel = await GameService.UpdateAndFetch(cancellationToken);

            GameManagement.SaveGameUpdateAndFetch(
                updateAndFetchResultModel.FetchModel,
                updateAndFetchResultModel.FetchOtherModel);

            var userProfilerModel = updateAndFetchResultModel.FetchOtherModel.UserProfileModel;

            // NOTE: MyIdを端末保存する
            PreferenceRepository.SetUserMyId(userProfilerModel.MyId);

            measurement.Report();
            progress.Report(1.0f);

            return updateAndFetchResultModel;
        }

        void InitPartyCacheRepository(IReadOnlyList<UserPartyModel> userPartyModels)
        {
            // NOTE: パーティキャッシュ情報にキャラ枠数の設定を行う
            UpdatePartyMemberSlot();
            var selectPartyNo = PreferenceRepository.SelectPartyNo;
            PartyCacheRepository.SetParties(userPartyModels, selectPartyNo);
        }

        void InitReceivedUnitIdsRepository(IReadOnlyList<UserUnitModel> userUnitModels)
        {
            // NOTE: 受け取り済みユニットIDの初期化
            var receivedUnitIds = userUnitModels
                .Select(model => model.MstUnitId)
                .Distinct()
                .ToList();

            AcquisitionDisplayedUnitIdsRepository.SetAcquisitionDisplayedUnitIds(receivedUnitIds);
        }

        void UpdatePartyMemberSlot()
        {
            var gameFetch = GameRepository.GetGameFetch();

            var mstPartyUnitCounts = MstPartyUnitCountDataRepository.GetPartyUnitCounts();
            var enablePartyUnitCount = mstPartyUnitCounts
                .Where(mst =>
                    mst.MstStageId.IsEmpty()
                    || gameFetch.StageModels.Any(stage => stage.MstStageId == mst.MstStageId && stage.ClearCount.Value > 0))
                .OrderByDescending(mst => mst.Count)
                .First();
            PartyCacheRepository.SetPartyMemberSlotCount(enablePartyUnitCount.Count);
        }

        void SaveDailyBonusInformation(
            IReadOnlyList<MissionReceivedDailyBonusModel> receivedDailyBonusModels,
            IReadOnlyList<MissionEventDailyBonusRewardModel> eventDailyBonusModels,
            IReadOnlyList<UserMissionEventDailyBonusProgressModel> userMissionEventDailyBonusProgressModels,
            IReadOnlyList<ComebackBonusRewardModel> comebackBonusRewardModels,
            IReadOnlyList<UserComebackBonusProgressModel> userComebackBonusProgressModels)
        {
            if (!receivedDailyBonusModels.IsEmpty())
            {
                // 受け取ったデイリーボーナスの日時の情報を保存
                // 前の情報が残ってても上書きする
                ReceivedDailyBonusRepository.Save(receivedDailyBonusModels);
            }

            // イベントデイリーボーナスの受け取り情報を保存
            if (!eventDailyBonusModels.IsEmpty())
            {
                ReceivedEventDailyBonusRepository.Save(eventDailyBonusModels);
            }
            else
            {
                if (userMissionEventDailyBonusProgressModels.IsEmpty())
                {
                    ReceivedEventDailyBonusRepository.Delete();
                }
            }
            
            // カムバックデイリーボーナスの受け取り情報を保存
            if (!comebackBonusRewardModels.IsEmpty())
            {
                ReceivedComebackDailyBonusRepository.Save(comebackBonusRewardModels);
            }
            else
            {
                if (userComebackBonusProgressModels.IsEmpty())
                {
                    ReceivedComebackDailyBonusRepository.Delete();
                }
            }
        }

        DownloadMetricsUseCaseModel GetDownloadMetricsUseCaseModel()
        {
            var downloadSize = new AssetDownloadSize((ulong)AssetManagement.GetAssetDownloadSize(FastFollowAssetKey));
            var freeSpaceSize = new FreeSpaceSize(StorageSupport.GetAvailableFreeSpace());

            ApplicationLog.Log(nameof(LoginUseCases), $"ディスク空き容量 {DataSizeConverter.ConvertToString(freeSpaceSize.Value)}");
            ApplicationLog.Log(nameof(LoginUseCases), $"ダウンロードサイズ {DataSizeConverter.ConvertToString(downloadSize.Value)}");

            // NOTE: アセットバンドルのマニュフェストファイルを取得し差分があるかを確認する
            //       差分がある場合アセットのダウンロードを行う
            return new DownloadMetricsUseCaseModel(downloadSize, freeSpaceSize);
        }
    }
}
