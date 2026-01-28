using System;
using System.Linq;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeStageLimitStatusView;
using GLOW.Scenes.StaminaBoostDialog.Presentation.View;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using GLOW.Scenes.StaminaRecover.Presentation;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class StageSelectPresenter : IStageSelectViewDelegate
    {
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] StageLimitStatusUseCase StageLimitStatusUseCase { get; }
        [Inject] GetStaminaUseCase GetStaminaUseCase { get; }
        [Inject] StartStageWireFrame StartStageWireFrame { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IHomeViewDelegate HomeViewDelegate { get; }
        [Inject] CheckStaminaBoostAvailabilityUseCase CheckStaminaBoostAvailabilityUseCase { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        readonly HomeMainPresenterSupport _presenterSupport = new();

        StaminaBoostDialogViewController _staminaBoostDialog;

        void IStageSelectViewDelegate.OnStartStageSelected(
            UIViewController viewController,
            MasterDataId mstStageId,
            UnlimitedCalculableDateTimeOffset mstStageEndAt,
            StagePlayableFlag playableFlag,
            StageConsumeStamina stageConsumeStamina,
            StageClearCount dailyClearCount,
            ClearableCount dailyPlayableCount,
            Action onStageStart)
        {
            // NOTE: 連打防止のため対応
            viewController.View.UserInteraction = false;
            HomeViewDelegate.ShowTapBlock(false, null, 0f);

            // 初期化
            _staminaBoostDialog = null;

            if (CheckStaminaBoostAvailabilityUseCase.CheckStaminaBoostAvailability(mstStageId))
            {
                var stageAvailable = GetStageAvailable(
                    mstStageId,
                    mstStageEndAt,
                    playableFlag,
                    stageConsumeStamina,
                    dailyClearCount,
                    dailyPlayableCount);

                // スタミナブーストダイアログ表示可能か
                if (CheckPlayableStage(stageAvailable))
                {
                    viewController.View.UserInteraction = true;
                    HomeViewDelegate.HideTapBlock(false, 0f);

                    // スタミナブーストダイアログ表示
                    ViewStaminaBoostDialog(
                        viewController,
                        mstStageId,
                        mstStageEndAt,
                        playableFlag,
                        stageConsumeStamina,
                        dailyClearCount,
                        dailyPlayableCount,
                        onStageStart);

                    return;
                }
            }

            StartStage(
                viewController,
                mstStageId,
                mstStageEndAt,
                playableFlag,
                stageConsumeStamina,
                dailyClearCount,
                dailyPlayableCount,
                onStageStart,
                StaminaBoostCount.One);
        }

        void StartStage(
            UIViewController viewController,
            MasterDataId mstStageId,
            UnlimitedCalculableDateTimeOffset mstStageEndAt,
            StagePlayableFlag playableFlag,
            StageConsumeStamina stageConsumeStamina,
            StageClearCount dailyClearCount,
            ClearableCount dailyPlayableCount,
            Action onStageStart,
            StaminaBoostCount staminaBoostCount)
        {
            var staminaBoostErrorAction = new Action(() =>
            {
                // スタミナブーストダイアログを閉じる
                if (_staminaBoostDialog != null)
                {
                    _staminaBoostDialog.Dismiss();
                    _staminaBoostDialog = null;
                }

                // ホームトップへ遷移
                TransitHomeTop();
            });

            var stageAvailable = GetStageAvailable(
                mstStageId,
                mstStageEndAt,
                playableFlag,
                stageConsumeStamina,
                dailyClearCount,
                dailyPlayableCount);

            if (stageAvailable != StageRequireStatus.Nothing)
            {
                if (stageAvailable == StageRequireStatus.UnRelease)
                {
                    _presenterSupport.ShowStageUnReleaseMessage(MessageViewUtil);
                }
                else if(stageAvailable == StageRequireStatus.OutOfAvailableTime)
                {
                    _presenterSupport.ShowStageUnReleaseOutOfTimeMessage(
                        MessageViewUtil,
                        staminaBoostErrorAction);
                }
                else if (stageAvailable == StageRequireStatus.StaminaLack)
                {
                    var argument = new StaminaRecoverySelectViewController.Argument(StaminaShortageFlag.True);
                    var controller = ViewFactory.Create<StaminaRecoverySelectViewController,
                        StaminaRecoverySelectViewController.Argument>(argument);

                    viewController.PresentModally(controller);
                }
                else if (stageAvailable == StageRequireStatus.InvalidParty)
                {
                    var specialRuleStatusUseCaseModel = StageLimitStatusUseCase.GetStageLimitStatusModel(mstStageId, false);
                    var partyViewModels = specialRuleStatusUseCaseModel.LimitStatus
                        .Select(StageLimitStatusViewModelTranslator.TranslateViewModel).ToList();
                    //HomeStageLimitStatusViewController
                    var controller =
                        ViewFactory
                            .Create<HomeStageLimitStatusViewController, HomeStageLimitStatusViewController.Argument>(
                                new HomeStageLimitStatusViewController.Argument()
                                {
                                    PartyName = specialRuleStatusUseCaseModel.PartyName,
                                    HomeStageLimitStatusViewModels = partyViewModels
                                });

                    viewController.PresentModally(controller);
                }
                else if (stageAvailable == StageRequireStatus.LimitDailyClear)
                {
                    _presenterSupport.ShowLimitDailyClearCountMessage(MessageViewUtil);
                }

                // NOTE: 選択できないステージの場合は操作できる状態に戻す
                viewController.View.UserInteraction = true;
                HomeViewDelegate.HideTapBlock(false, 0f);
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
                return;
            }

            StartStageWireFrame.StartStage(viewController.View, mstStageId, staminaBoostCount, didStart =>
            {
                if (didStart)
                {
                    onStageStart?.Invoke();
                    return;
                }
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);

                // NOTE: 開始できない場合は操作できる状態に戻す
                viewController.View.UserInteraction = true;
                HomeViewDelegate.HideTapBlock(false, 0f);
            });
        }

        StageRequireStatus GetStageAvailable(
            MasterDataId mstStageId,
            UnlimitedCalculableDateTimeOffset mstStageEndAt,
            StagePlayableFlag playableFlag,
            StageConsumeStamina stageConsumeStamina,
            StageClearCount dailyClearCount,
            ClearableCount dailyPlayableCount)
        {
            var userStamina = GetStaminaUseCase.GetUserStamina();
            var invalidPartyModel = StageLimitStatusUseCase.GetStageLimitStatusModel(mstStageId, true);
            var invalidPartyViewModels = invalidPartyModel.LimitStatus
                .Select(StageLimitStatusViewModelTranslator.TranslateViewModel).ToList();

            return _presenterSupport.CheckStageAvailable(
                playableFlag.Value,
                stageConsumeStamina,
                userStamina.Stamina,
                invalidPartyViewModels,
                TimeProvider.Now,
                mstStageEndAt,
                dailyClearCount,
                dailyPlayableCount
            );
        }

        void ViewStaminaBoostDialog(
            UIViewController viewController,
            MasterDataId mstStageId,
            UnlimitedCalculableDateTimeOffset mstStageEndAt,
            StagePlayableFlag playableFlag,
            StageConsumeStamina stageConsumeStamina,
            StageClearCount dailyClearCount,
            ClearableCount dailyPlayableCount,
            Action onStageStart)
        {
            // スタミナブースト利用可能なステージの場合は、スタミナブースト選択画面へ遷移
            var args = new StaminaBoostDialogViewController.Argument(mstStageId);
            _staminaBoostDialog = ViewFactory
                .Create<StaminaBoostDialogViewController, StaminaBoostDialogViewController.Argument>(args);
            _staminaBoostDialog.OnStartButtonTappedAction = (isEnoughStamina, staminaBoostCount) =>
            {
                if (isEnoughStamina)
                {
                    // スタミナが足りている場合はステージ開始処理へ
                    StartStage(
                        viewController,
                        mstStageId,
                        mstStageEndAt,
                        playableFlag,
                        stageConsumeStamina,
                        dailyClearCount,
                        dailyPlayableCount,
                        onStageStart,
                        staminaBoostCount);
                }
                else
                {
                    // スタミナが足りていない場合はスタミナ回復画面へ遷移
                    var argument = new StaminaRecoverySelectViewController.Argument(StaminaShortageFlag.True);
                    var controller = ViewFactory.Create<StaminaRecoverySelectViewController,
                        StaminaRecoverySelectViewController.Argument>(argument);

                    controller.OnDismissAction = () =>
                    {
                        // スタミナブーストダイアログを更新
                        _staminaBoostDialog.BeginAppearanceTransition(true, false);
                    };
                    viewController.PresentModally(controller);
                }
            };
            viewController.PresentModally(_staminaBoostDialog);
        }

        bool CheckPlayableStage(StageRequireStatus stageAvailable)
        {
            return stageAvailable != StageRequireStatus.UnRelease &&
                   stageAvailable != StageRequireStatus.OutOfAvailableTime &&
                   stageAvailable != StageRequireStatus.InvalidParty &&
                   stageAvailable != StageRequireStatus.LimitDailyClear;
        }

        void TransitHomeTop()
        {
            if (HomeViewNavigation.CurrentContentType == HomeContentTypes.Main)
            {
                HomeViewNavigation.TryPopToRoot();
            }
            else
            {
                HomeViewNavigation.Switch(HomeContentTypes.Main);
            }
        }

    }
}
