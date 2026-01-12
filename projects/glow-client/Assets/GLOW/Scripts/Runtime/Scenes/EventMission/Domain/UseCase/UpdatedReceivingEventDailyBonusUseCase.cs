using System.Collections.Generic;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EventMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Creator;
using Zenject;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public class UpdatedReceivingEventDailyBonusUseCase
    {
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }

        public EventMissionDailyBonusResultModel UpdateReceivingEventDailyBonus(MasterDataId mstEventId)
        {
            var openEvent = MstEventDataRepository.GetEventFirstOrDefault(mstEventId);
            if (openEvent.IsEmpty())
            {
                return EventMissionDailyBonusResultModel.Empty;
            }

            var eventDailyScheduleModel = MissionDataRepository.GetMstMissionEventDailyBonusScheduleModelFirstOrDefault(mstEventId);
            if (eventDailyScheduleModel.IsEmpty())
            {
                return EventMissionDailyBonusResultModel.Empty;
            }
            
            var fetchOtherModel = ResetMissionEventDailyBonusRewards();
            
            var eventDailyBonusModel = MissionResultModelFactory.CreateEventMissionDailyBonusResultModel(
                openEvent,
                MissionDataRepository,
                MissionDataRepository,
                PlayerResourceModelFactory,
                eventDailyScheduleModel.Id,
                fetchOtherModel.MissionEventDailyBonusRewardModels,
                fetchOtherModel.UserMissionEventDailyBonusProgressModels);
            return eventDailyBonusModel;
        }

        GameFetchOtherModel ResetMissionEventDailyBonusRewards()
        {
            var fetchOther = GameRepository.GetGameFetchOther() with
            {
                // 受け取り演出時は含まれている状態、演出後リセット
                MissionEventDailyBonusRewardModels = new List<MissionEventDailyBonusRewardModel>()
            };
            GameManagement.SaveGameFetchOther(fetchOther);
            
            return fetchOther;
        }
    }
}
