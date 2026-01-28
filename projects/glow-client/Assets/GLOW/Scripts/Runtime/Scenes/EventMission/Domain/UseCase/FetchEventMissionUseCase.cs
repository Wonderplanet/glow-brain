using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Loader;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public class FetchEventMissionUseCase
    {
        [Inject] IMissionService MissionService { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IMissionEventCacheRepository MissionEventCacheRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }
        [Inject] IMissionEventCacheModelFactory MissionEventCacheModelFactory { get; }
        [Inject] IReceivedDailyBonusRewardLoader ReceivedDailyBonusRewardLoader { get; }

        const int EventDailyBonusDisplayScheduleAdditionalHours = 24;

        public async UniTask<EventMissionFetchResultModel> UpdateAndFetchEventMissionList(
            MasterDataId mstEventId,
            bool isDisplayedInHome,
            CancellationToken cancellationToken)
        {
            await UpdateAndFetch(cancellationToken);//ここ副作用

            return isDisplayedInHome ? GetModelFromAllEvent() : GetModelFromSingleEvent(mstEventId);
        }

        EventMissionFetchResultModel GetModelFromSingleEvent(MasterDataId mstEventId)
        {
            var mstEventModel = MstEventDataRepository.GetEvent(mstEventId);
            if (mstEventModel.IsEmpty()
                || !CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mstEventModel.StartAt, mstEventModel.EndAt))
            {
                return EventMissionFetchResultModel.Empty;
            }
            var eventMissionModel = MissionEventCacheRepository.GetMissionEventModelOrDefault(mstEventModel.Id);

            return new EventMissionFetchResultModel(
                mstEventModel.Id,
                GetEventMissionAchievementResultModel(
                    new List<MstEventModel>(){mstEventModel},
                    eventMissionModel.UserMissionEventModels),
                GetEventMissionDailyBonusResultModel(mstEventModel));
        }

        EventMissionFetchResultModel GetModelFromAllEvent()
        {
            var mstEventModels = MstEventDataRepository
                .GetEvents()
                .Where(m => CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    m.StartAt,
                    m.EndAt))
                .ToList();
            if(mstEventModels.Count == 0)
            {
                return EventMissionFetchResultModel.Empty;
            }

            var eventMissionModels = mstEventModels
                .Select(m => MissionEventCacheRepository.GetMissionEventModelOrDefault(m.Id))
                .ToList();

            var userMissionEventModels = eventMissionModels
                .SelectMany(m => m.UserMissionEventModels)
                .ToList();

            var newestMstEventModel = mstEventModels.MaxBy(m => m.StartAt);
            return new EventMissionFetchResultModel(
                newestMstEventModel.Id,
                GetEventMissionAchievementResultModel(mstEventModels,userMissionEventModels),
                GetEventMissionDailyBonusResultModel(newestMstEventModel));
        }

        async UniTask UpdateAndFetch(CancellationToken cancellationToken)
        {
            var updateAndFetchResultModel = await MissionService.EventUpdateAndFetch(cancellationToken);

            var missionEventCacheModel = MissionEventCacheModelFactory.Create(updateAndFetchResultModel);
            MissionEventCacheRepository.SetMissionEventCacheModel(missionEventCacheModel);
        }

        EventMissionAchievementResultModel GetEventMissionAchievementResultModel(
            IReadOnlyList<MstEventModel> mstEventModels,
            IReadOnlyList<UserMissionEventModel> userEventAchievementModel)
        {
            return MissionResultModelFactory.CreateEventMissionAchievementResultModel(
                mstEventModels,
                TimeProvider,
                MissionDataRepository,
                MissionDataRepository,
                PlayerResourceModelFactory,
                userEventAchievementModel);
        }

        EventMissionDailyBonusResultModel GetEventMissionDailyBonusResultModel(MstEventModel mstEventModel)
        {
            var eventDailyScheduleModel = MissionDataRepository.GetMstMissionEventDailyBonusScheduleModelFirstOrDefault(mstEventModel.Id);
            if (eventDailyScheduleModel.IsEmpty())
            {
                return EventMissionDailyBonusResultModel.Empty;
            }
            
            ReceivedDailyBonusRewardLoader.LoadReceivedEventDailyBonusRewards();
            
            var endDateTime = eventDailyScheduleModel.EndAt;
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            
            if (!fetchOtherModel.MissionEventDailyBonusRewardModels.IsEmpty())
            {
                // 演出再生ができる状態の場合は1日アディショナルタイムを設ける
                endDateTime = endDateTime.AddHours(EventDailyBonusDisplayScheduleAdditionalHours);
            }

            // 取得したログボの期間が現在の時間的に有効ではない場合はEmpty
            if (!CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    eventDailyScheduleModel.StartAt,
                    endDateTime))
            {
                return EventMissionDailyBonusResultModel.Empty;
            }
            
            return MissionResultModelFactory.CreateEventMissionDailyBonusResultModel(
                mstEventModel,
                MissionDataRepository,
                MissionDataRepository,
                PlayerResourceModelFactory,
                eventDailyScheduleModel.Id,
                fetchOtherModel.MissionEventDailyBonusRewardModels,
                fetchOtherModel.UserMissionEventDailyBonusProgressModels);
        }
    }
}
