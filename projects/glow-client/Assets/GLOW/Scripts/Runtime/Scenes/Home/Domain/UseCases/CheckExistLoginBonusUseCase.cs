using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class CheckExistLoginBonusUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IReceivedDailyBonusRepository ReceivedDailyBonusRepository { get; }
        [Inject] IReceivedEventDailyBonusRepository ReceivedEventDailyBonusRepository { get; }
        [Inject] IMstMissionEventDataRepository MstMissionEventDataRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMissionService MissionService { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        
        public async UniTask<DisplayAtLoginFlag> IsExistLoginBonus(MissionType missionType, CancellationToken cancellationToken)
        {
            switch (missionType)
            {
                case MissionType.DailyBonus:
                    var dailyBonus = GameRepository.GetGameFetchOther().MissionReceivedDailyBonusModel;
                    var isExistDailyBonus = ReceivedDailyBonusRepository.IsExist();
                    return (!dailyBonus.IsEmpty() || isExistDailyBonus) 
                        ? DisplayAtLoginFlag.True 
                        : DisplayAtLoginFlag.False;
                case MissionType.EventDailyBonus:
                    await TryReceiveFirstDayEventDailyBonus(cancellationToken);
                    var eventDailyBonus = GameRepository.GetGameFetchOther().MissionEventDailyBonusRewardModels;
                    var isExistEventDailyBonus = ReceivedEventDailyBonusRepository.IsExist();
                    return (!eventDailyBonus.IsEmpty() || isExistEventDailyBonus) 
                        ? DisplayAtLoginFlag.True 
                        : DisplayAtLoginFlag.False;
                default:
                    return DisplayAtLoginFlag.False;
            }
        }

        async UniTask TryReceiveFirstDayEventDailyBonus(CancellationToken cancellationToken)
        {
            var canReceiveFirstDayEventDailyBonus = CanReceiveFirstDayEventDailyBonus();
            if (!canReceiveFirstDayEventDailyBonus) return;
            
            var result = await MissionService.ReceiveEventDailyBonusUpdate(cancellationToken);
            
            // ログインボーナスで獲得した報酬等をGameFetch/FetchOtherに反映(副作用)
            UpdateUserLevelUpCacheAndGameFetchOther(result);
        }
        
        bool CanReceiveFirstDayEventDailyBonus()
        {
            var mstEventIds = MstEventDataRepository.GetEvents()
                .Where(model => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, model.StartAt, model.EndAt))
                .Select(model => model.Id)
                .ToList();
            
            // 対象のイベントデイリーボーナススケジュールが存在しない場合は処理を終了
            if (mstEventIds.IsEmpty()) return false;
            
            // イベントデイリーボーナススケジュールのうち、スケジュール期間内のものを抽出
            var eventDailyBonusScheduleIds = mstEventIds
                .Select(eventId => MstMissionEventDataRepository.GetMstMissionEventDailyBonusScheduleModelFirstOrDefault(eventId))
                .Where(model => !model.IsEmpty())
                .Where(model => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, model.StartAt, model.EndAt))
                .Select(model => model.Id)
                .ToList();
            
            // 対象のイベントデイリーボーナススケジュールが存在しない場合は処理を終了
            if (eventDailyBonusScheduleIds.IsEmpty()) return false;
            
            // eventDailyBonusScheduleIdsのIDがGameFetchOtherのUserMissionEventDailyBonusProgressModelに含まれていれば処理を終了
            var userMissionEventDailyBonusProgressModelIds = GameRepository.GetGameFetchOther()
                .UserMissionEventDailyBonusProgressModels
                .Select(model => model.MstMissionEventDailyBonusScheduleId)
                .ToList();
            var isAllFirstDailyBonusReceived = eventDailyBonusScheduleIds.All(id => userMissionEventDailyBonusProgressModelIds.Contains(id));
            
            return !isAllFirstDailyBonusReceived;
        }
        
        void UpdateUserLevelUpCacheAndGameFetchOther(MissionEventDailyBonusUpdateResultModel result)
        {
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
                MissionEventDailyBonusRewardModels = result.EventDailyBonusRewardModels,
                UserMissionEventDailyBonusProgressModels = result.UserMissionEventDailyBonusProgressModels,
                UserUnitModels = prevFetchOtherModel.UserUnitModels.Update(result.UserUnitModels),
                UserItemModels = prevFetchOtherModel.UserItemModels.Update(result.UserItemModels),
                UserEmblemModel = prevFetchOtherModel.UserEmblemModel.Update(result.UserEmblemModels),
                UserConditionPackModels = prevFetchOtherModel.UserConditionPackModels.Update(result.ConditionPackModels)
            };

            GameManagement.SaveGameUpdateAndFetch(newFetchModel, newFetchOtherModel);
        }
    }
}