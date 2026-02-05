using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Tracker;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.Evaluator;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    /// <summary>
    /// 導入・メインパートの進捗更新を行う
    /// </summary>
    public class ProgressTutorialStatusUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] ITutorialService TutorialService { get; }
        [Inject] IAnalyticsTracker AnalyticsTracker { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }

#if GLOW_DEBUG
        public void UpdateTutorialStatus(TutorialStatusModel newTutorialStatus)
        {
            var preFetchOtherModel = GameRepository.GetGameFetchOther();

            var newFetchOtherModel = preFetchOtherModel with { TutorialStatus = newTutorialStatus };

            GameManagement.SaveGameFetchOther(newFetchOtherModel);
        }
#endif // GLOW_DEBUG

        public async UniTask ProgressTutorialStatus(CancellationToken cancellationToken)
        {
            var nextTutorialModel = TutorialEvaluator.GetNextMstTutorialModel(GameRepository, MstTutorialRepository);

            // 進捗更新API
            var result = await TutorialService.UpdateTutorialStatus(cancellationToken, nextTutorialModel.TutorialFunctionName);

            var newTutorialStatus = new TutorialStatusModel(nextTutorialModel.TutorialFunctionName);

            var myId = GameRepository.GetGameFetchOther().UserProfileModel.MyId;

            // チュートリアルが完了したかどうか
            if (newTutorialStatus.IsCompleted())
            {
                // Adjustでイベントを送る
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
                UserConditionPackModels = prevFetchOtherModel.UserConditionPackModels.Update(result.UserConditionPackModels)
            };

            GameManagement.SaveGameUpdateAndFetch(newFetchModel, newFetchOtherModel);
        }
    }
}
