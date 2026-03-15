using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.Tracker;
using GLOW.Modules.Tutorial.Domain.Applier;
using GLOW.Modules.Tutorial.Domain.Definitions;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class MigrateTutorialStatusUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] ITutorialService TutorialService { get; }
        [Inject] IAnalyticsTracker AnalyticsTracker { get; }
        [Inject] IStageService StageService { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] ITutorialGachaConfirmedApplier TutorialGachaConfirmedApplier { get; }
        
        public async UniTask UpdateTutorialStatusForRenewal(CancellationToken cancellationToken)
        {
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            
            // 完了済みの場合はスキップ
            if (tutorialStatus.IsCompleted()) return;
            
            // 新規チュートリアルの場合はスキップ
            if (tutorialStatus.IsRenewalTutorialStatus()) return;
            
            
            // 現在のステータスに応じた新規ステータスへ更新
            if (tutorialStatus.IsInitial())
            {
                // 名前決定前の初期状態は新規の開始時ステータスへ更新
                var nextTutorialModel = TutorialSequenceIdDefinitions.NewTutorialStart;
                await UpdateAndApplyTutorialStatus(cancellationToken, nextTutorialModel);
            }
            else if (tutorialStatus.IsBeforeGacha())
            {
                // 名前決定後~ガシャ前は刷新後チュートリアルステージ開始ステータスへ
                var nextTutorialModel = TutorialSequenceIdDefinitions.NewStartInGame;
                await UpdateAndApplyTutorialStatus(cancellationToken, nextTutorialModel);
            }
            else
            {
                // チュートリアルインゲーム中の場合破棄する 破棄済みでも問題なし
                if (tutorialStatus.IsStartInGame1() || tutorialStatus.IsStartInGame2())
                {
                    await StageService.AbortSession(cancellationToken, StageAbortType.Retire);
                }
                
                // ガシャ後にパーティ編成・アバター設定ができていなかった場合に設定する
                await TutorialGachaConfirmedApplier.ApplyPartyAndAvatarIfNeeds(cancellationToken);
                
                
                // ガシャ以降のステータスはチュートリアル完了へ(チュートリアル進行状態を変更するため、最後に行う)
                var nextTutorialModel = TutorialSequenceIdDefinitions.TutorialMainPart_completeTutorial;
                await UpdateAndApplyTutorialStatus(cancellationToken, nextTutorialModel);
                
                // チュートリアルを強制完了させるため、Adjustでイベントを送る
                var myId = GameRepository.GetGameFetchOther().UserProfileModel.MyId;
                var dictionary = new Dictionary<string, object>()
                {
                    {TrackEventNameDefinitions.AppUserId, myId.Value}
                };
                AnalyticsTracker.TrackAdjustEvent(
                    TrackEventNameDefinitions.AdjustTutorial,
                    dictionary);
                AnalyticsTracker.TrackFirebaseAnalyticsEvent(
                    TrackEventNameDefinitions.FirebaseTutorial,
                    dictionary);
            }
        }

        async UniTask UpdateAndApplyTutorialStatus(
            CancellationToken cancellationToken,
            TutorialStatusModel newTutorialStatus)
        {
            // チュートリアルステータス更新API
            var result = await TutorialService.UpdateTutorialStatus(
                cancellationToken,
                newTutorialStatus.TutorialFunctionName);

            // ローカルのチュートリアルステータスを更新
            var prevFetchModel = GameRepository.GetGameFetch();
            var newFetchModel = prevFetchModel with
            {
                UserParameterModel = result.UserParameterModel
            };

            UserLevelUpCacheRepository.Save(
                result.UserLevelUpResultModel,
                prevFetchModel.UserParameterModel.Level,
                prevFetchModel.UserParameterModel.Exp);

            var prevFetchOtherModel = GameRepository.GetGameFetchOther();

            // チュートリアル進行に伴い各種ユーザーモデルを更新する
            // メインパート終了後に初めてログインボーナスを受け取る場合があるため
            var newFetchOtherModel = prevFetchOtherModel with
            {
                TutorialStatus = newTutorialStatus,
                UserGachaModels = prevFetchOtherModel.UserGachaModels.Update(result.UserGachaModels),
                UserIdleIncentiveModel = result.UserIdleIncentiveModel.IsEmpty()
                    ? prevFetchOtherModel.UserIdleIncentiveModel
                    : result.UserIdleIncentiveModel,
                MissionReceivedDailyBonusModel = result.MissionReceivedDailyBonusModel,
                MissionEventDailyBonusRewardModels = result.MissionEventDailyBonusRewardModels,
                UserMissionEventDailyBonusProgressModels = result.UserMissionEventDailyBonusProgressModels,
                UserUnitModels = prevFetchOtherModel.UserUnitModels.Update(result.UserUnitModels),
                UserItemModels = prevFetchOtherModel.UserItemModels.Update(result.UserItemModels),
                UserEmblemModel = prevFetchOtherModel.UserEmblemModel.Update(result.UserEmblemModels),
                UserConditionPackModels =
                prevFetchOtherModel.UserConditionPackModels.Update(result.UserConditionPackModels)
            };

            GameManagement.SaveGameUpdateAndFetch(newFetchModel, newFetchOtherModel);
        }
    }
}

