using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Transitions;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Login.Domain.UseCase;
using GLOW.Scenes.Login.Domain.UseCases;
using GLOW.Scenes.Title.Domains.UseCase;
using WonderPlanet.SceneManagement;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.Title.Presentations.WireFrame
{
    public class SessionResumePresenter : ISessionResumeApproval
    {
        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] ISelectStageUseCase SelectStageUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] SessionAbortUseCase
            SessionAbortUseCase { get; }
        [Inject] StageSessionResumeUseCase StageSessionResumeUseCase { get; }
        [Inject] PvpSessionResumeUseCase PvpSessionResumeUseCase { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] TitleWireFrame TitleWireFrame { get; }
        [Inject] IPeriodOutsideExceptionWireframe PeriodOutsideExceptionWireframe { get; }
        [Inject] SessionResumeStateRegistrationUseCase SessionResumeStateRegistrationUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] GetContentMaintenanceTypeUseCase GetContentMaintenanceTypeUseCase { get; }
        [Inject] SessionCleanupUseCase SessionCleanupUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }

        const string AbortConfirmDescription = "バトルを再開せず\nホーム画面に移動しますか？";

        async UniTask<bool> ISessionResumeApproval.ShowResumeSessionConfirmView(
            CancellationToken cancellationToken,
            InGameContentType inGameContentType,
            MasterDataId targetMstId)
        {
            var completionSource = new UniTaskCompletionSource<bool>();
            if (targetMstId.IsEmpty()) return await UniTask.FromResult(true);

            // 中断復帰するコンテンツが部分メンテ中か確認
            var contentMaintenanceType = GetContentMaintenanceTypeUseCase.GetContentMaintenanceType(inGameContentType, targetMstId);
            bool isMaintenance = CheckContentMaintenanceUseCase.IsInMaintenance(
                new []{ new ContentMaintenanceTarget(
                    contentMaintenanceType,
                    MasterDataId.Empty) });
            if (isMaintenance)
            {
                // 部分メンテ中の場合
                await ContentMaintenanceWireframe.ShowDialogForResumeAsync(contentMaintenanceType, cancellationToken);

                await SessionCleanupUseCase.CleanupSession(cancellationToken, inGameContentType);
                completionSource.TrySetResult(true);
                TitleWireFrame.SwitchHomeScene();
            }
            else
            {
                // 部分メンテ中でない場合
                var model = StageSessionResumeUseCase.GetModel(inGameContentType, targetMstId);
                if (model.IsOpenStage)
                {
                    // ダイアログ操作待ち
                    ShowResumeView(
                        cancellationToken,
                        completionSource,
                        inGameContentType,
                        targetMstId, model.SessionAbortConfirmAttentionText);
                }
                else
                {
                    ShowForceAbortView(cancellationToken, inGameContentType, targetMstId, completionSource);
                }
            }

            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());
            return await completionSource.Task;
        }

        void ShowResumeView(
            CancellationToken cancellationToken,
            UniTaskCompletionSource<bool> completionSource,
            InGameContentType inGameContentType,
            MasterDataId targetMstId,
            SessionAbortConfirmAttentionText attentionText)
        {
            MessageViewUtil.ShowMessageWith2Buttons(
                "バトル再開確認",
                "バトル中にアプリが終了しました\nバトルを再開しますか？",
                "",
                "はい",
                "キャンセル",
                () =>
                {
                    DoAsync.Invoke(cancellationToken, async (ct) =>
                    {
                        try
                        {
                            await TransitInGame(ct, inGameContentType, targetMstId);
                            completionSource.TrySetResult(true);
                        }
                        catch (ContentMaintenanceNeedCleanupException)
                        {
                            // 部分メンテナンスが発生した場合、Cleanupしてホームへ
                            await Cleanup(ct, completionSource, inGameContentType, targetMstId);
                        }
                    });
                },
                () =>
                {
                    ShowAbortConfirmView(
                        cancellationToken,
                        completionSource,
                        inGameContentType,
                        targetMstId,
                        attentionText);
                },
                () =>
                {
                    ShowAbortConfirmView(
                        cancellationToken,
                        completionSource,
                        inGameContentType,
                        targetMstId,
                        attentionText);
                });
        }

        async UniTask TransitInGame(
            CancellationToken cancellationToken,
            InGameContentType inGameContentType,
            MasterDataId targetMstId)
        {
            SessionResumeStateRegistrationUseCase.SaveResumableState(inGameContentType, targetMstId);
            switch (inGameContentType)
            {
                case InGameContentType.AdventBattle:
                    // 副作用
                    SelectStageUseCase.SelectStage(MasterDataId.Empty, targetMstId, ContentSeasonSystemId.Empty);
                    break;
                case InGameContentType.Pvp:
                    // 副作用。pvpは内部でSelectStageしている
                    await PvpSessionResumeUseCase.ResumePvp(cancellationToken);
                    break;
                case InGameContentType.Stage:
                default:
                    // 副作用
                    SelectStageUseCase.SelectStage(targetMstId, MasterDataId.Empty, ContentSeasonSystemId.Empty);
                    break;
            }

            SceneNavigation.Switch<InGameTransition>(default, "InGame").Forget();
        }

        void ShowForceAbortView(
            CancellationToken cancellationToken,
            InGameContentType inGameContentType,
            MasterDataId targetMstId,
            UniTaskCompletionSource<bool> completionSource)
        {
            PeriodOutsideExceptionWireframe.ShowForceAbortMessage(
                () =>
                {
                    DoAsync.Invoke(cancellationToken, ScreenInteractionControl, async (ct) =>
                    {
                        try
                        {
                            await SessionAbortUseCase.OnAbortSession(ct, inGameContentType); //pvpのとき副作用あり
                            completionSource.TrySetResult(true);
                            TitleWireFrame.SwitchHomeScene();
                        }
                        catch (ContentMaintenanceNeedCleanupException)
                        {
                            // 部分メンテナンスが発生した場合、Cleanupしてホームへ
                            await Cleanup(ct, completionSource, inGameContentType, targetMstId);
                        }
                    });
                },
                inGameContentType);
        }

        void ShowAbortConfirmView(
            CancellationToken cancellationToken,
            UniTaskCompletionSource<bool> completionSource,
            InGameContentType inGameContentType,
            MasterDataId targetMstId,
            SessionAbortConfirmAttentionText attentionText)
        {
            MessageViewUtil.ShowMessageWith2Buttons(
                "バトル再開確認",
                AbortConfirmDescription,
                attentionText.Value,
                "はい",
                "キャンセル",
                () =>
                {
                    DoAsync.Invoke(cancellationToken, ScreenInteractionControl, async (ct) =>
                    {
                        try
                        {
                            await SessionAbortUseCase.OnAbortSession(ct, inGameContentType); //pvpのとき副作用あり
                            // 降臨バトルからのリタイアだと挑戦回数の更新が必要
                            LocalNotificationScheduler.RefreshRemainAdventBattleCountSchedule(); // 副作用
                            completionSource.TrySetResult(true);
                            TitleWireFrame.SwitchHomeScene();
                        }
                        catch (ContentMaintenanceNeedCleanupException)
                        {
                            // 部分メンテナンスが発生した場合、Cleanupしてホームへ
                            await Cleanup(ct, completionSource, inGameContentType, targetMstId);
                        }
                    });
                },
                () =>
                {
                    ShowResumeView(
                        cancellationToken,
                        completionSource,
                        inGameContentType,
                        targetMstId,
                        attentionText);
                },
                () =>
                {
                    ShowResumeView(
                        cancellationToken,
                        completionSource,
                        inGameContentType,
                        targetMstId,
                        attentionText);
                }
            );
        }

        async UniTask Cleanup(
            CancellationToken cancellationToken,
            UniTaskCompletionSource<bool> completionSource,
            InGameContentType inGameContentType,
            MasterDataId targetMstId)
        {
            // ダイアログ表示
            var contentMaintenanceType = GetContentMaintenanceTypeUseCase.GetContentMaintenanceType(inGameContentType, targetMstId);
            await ContentMaintenanceWireframe.ShowDialogForResumeAsync(contentMaintenanceType, cancellationToken);

            // Cleanup
            await SessionCleanupUseCase.CleanupSession(cancellationToken, inGameContentType);

            // ホーム遷移
            // ※ContentMaintenanceOutside発生した場合はタイトルへ、ServerErrorExceptionHandler参照
            completionSource.TrySetResult(true);
            TitleWireFrame.SwitchHomeScene();
        }

    }
}
