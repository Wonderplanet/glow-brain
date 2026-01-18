using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.SceneNavigation;
using GLOW.Core.Presentation.Transitions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.AdventBattle.Domain.UseCase;
using GLOW.Scenes.AdventBattle.Presentation.Presenter;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.InGame.Domain.UseCases;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.Title.Domains.UseCase;
using WonderPlanet.SceneManagement;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Navigation
{
    public class InGameRetrySceneNavigator : IInGameRetrySceneNavigator
    {
        [Inject] AdventBattleWireFrame AdventBattleWireFrame { get; }
        [Inject] AdventBattleStartUseCase AdventBattleStartUseCase { get; }
        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] IGlowSceneNavigation GlowSceneNavigation { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] ISelectStageUseCase SelectStageUseCase { get; }
        [Inject] HomeStartStageUseCase StartStageUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] RetryPeriodOutsideUseCase RetryPeriodOutsideUseCase { get; }
        
        async UniTask IInGameRetrySceneNavigator.RetryStage(
            CancellationToken cancellationToken,
            StaminaBoostCount staminaBoostCount,
            AdChallengeFlag isAdChallenge)
        {
            var selectedStage = SelectedStageEvaluator.GetSelectedStage();
            var selectedId = selectedStage.SelectedId;
            try
            {
                if (selectedStage.InGameType == InGameType.Normal)
                {
                    // ノーマル・イベント、コイン獲得クエスト再挑戦
                    await RetryNormalStage(cancellationToken, selectedId, staminaBoostCount, isAdChallenge);
                }
                else if (selectedStage.InGameType == InGameType.AdventBattle)
                {
                    // 降臨バトル再挑戦
                    await AdventBattleStart(cancellationToken, selectedId, isAdChallenge);
                }
                else
                {
                    // ランクマッチ再挑戦は仕様上あり得ないが、念のためホームへ戻す
                    ShowInvalidMessage(TransitionToHomeView);
                }
            }
            catch (Exception e) when (
                e is StageCanNotStartException
                    or AdventBattleCannotStartException)
            {
                // 開放できないステージ ホームに戻る
                ShowCanNotStartMessage(TransitionToHomeView);
            }
            catch (LackOfResourcesException)
            {
                // スタミナ不足 - ホームに戻る
                ShowStaminaLessMessage(TransitionToHomeView);
            }
            catch (Exception e) when (
                e is QuestPeriodOutsideException
                    or EventPeriodOutsideException
                    or PvpPeriodOutsideException
                    or AdventBattlePeriodOutsideException)
            {
                // 戻り先・コンティニュー情報のクリア
                RetryPeriodOutsideUseCase.ClearResumableState();
                
                // クエストやイベントの開催期間が終了している場合はホームに遷移
                ShowPeriodOutsideMessage(TransitionToHomeView);
            }
        }

        async UniTask RetryNormalStage(
            CancellationToken cancellationToken,
            MasterDataId mstStageId,
            StaminaBoostCount staminaBoostCount,
            AdChallengeFlag isAdChallenge)
        {
            SelectStageUseCase.SelectStage(mstStageId, MasterDataId.Empty, ContentSeasonSystemId.Empty);
            
            await StartStageUseCase.StartStage(cancellationToken, mstStageId, staminaBoostCount, isAdChallenge);
            LocalNotificationScheduler.RefreshRemainCoinQuestSchedule();
            SoundEffectPlayer.Play(SoundEffectId.SSE_012_003);

            // NOTE: リトライ処理中にInGameシーンが破棄されてCancellationTokenが
            // キャンセルされる可能性があるため、CancellationToken.Noneを使用
            await GlowSceneNavigation.SwitchViaIntermediateScene<InGameTransition>(
                CancellationToken.None,
                "InGameRetryIntermediate",
                "InGame");
            
        }
        
        async UniTask AdventBattleStart(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId,
            bool isAdChallenge)
        {
            var challengeType = isAdChallenge
                ? AdventBattleChallengeType.Advertisement
                : AdventBattleChallengeType.Normal;
            
            var resultModel = await AdventBattleStartUseCase.StartAdventBattle(
                cancellationToken,
                mstAdventBattleId,
                challengeType);

            if (resultModel.ErrorType == AdventBattleErrorType.None)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_012_003);
                
                // NOTE: リトライ処理中にInGameシーンが破棄されてCancellationTokenがキャンセルされる可能性があるため、
                // シーン遷移にはCancellationToken.Noneを使用してキャンセル不可能にする
                await GlowSceneNavigation.SwitchViaIntermediateScene<InGameTransition>(
                    CancellationToken.None, 
                    "InGameRetryIntermediate", 
                    "InGame");
            }
            else
            {
                // NOTE: 挑戦できない場合はホームへ戻す
                ShowAdventBattleErrorView(resultModel);
            }
        }
        
        void ShowAdventBattleErrorView(AdventBattleStartUseCaseResultModel resultModel)
        {
            if (resultModel.ErrorType == AdventBattleErrorType.InvalidParty)
            {
                // 挙動上あり得ないが、ダイアログ表示してホーム遷移
                ShowInvalidPartyMessage(TransitionToHomeView);

            }
            else if (resultModel.ErrorType == AdventBattleErrorType.OutOfTime)
            {
                // 期間外
                AdventBattleWireFrame.ShowCloseMessage(TransitionToHomeView);
            }
            else if (resultModel.ErrorType == AdventBattleErrorType.OverChallengeCount)
            {
                // 挑戦回数上限
                AdventBattleWireFrame.ShowLimitChallengeMessage(TransitionToHomeView);
            }
        }
        
        void TransitionToHomeView()
        {
            SceneNavigation.Switch<HomeTopTransition>(default, "Home").Forget();
        }

        void ShowInvalidMessage(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "現在挑戦することができません。",
                "",
                onClose: onClose);
            
        }
        
        void ShowCanNotStartMessage(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "挑戦できないステージです。",
                "",
                onClose: onClose);
        }
        
        void ShowStaminaLessMessage(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "スタミナが不足しています。",
                "",
                onClose: onClose);
        }
        
        void ShowInvalidPartyMessage(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose(
                "編成できないキャラがいます",
                "特別ルールがあります。\n特別ルールの条件を満たすと挑戦可能になります。",
                "",
                onClose: onClose);
        }
        
        void ShowPeriodOutsideMessage(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "開催期間が終了しています。\n次回開催をお待ちください。",
                "",
                onClose: onClose);
        }
    }
}