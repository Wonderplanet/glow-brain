using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EventMission.Domain.Model;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using Zenject;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public class GetEventMissionTimeInformationUseCase
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstMissionEventDataRepository MstMissionEventDataRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        public EventMissionTimeUseCaseModel GetEventMissionTimeInformation(MasterDataId mstEventId)
        {
            if (mstEventId.IsEmpty()) return EventMissionTimeUseCaseModel.Empty;

            var nowTime = TimeProvider.Now;
            
            // イベントミッションの場合はイベントの終了時刻
            var eventEndAt = MstEventDataRepository.GetEventFirstOrDefault(mstEventId).EndAt;
            
            // イベントログインボーナスの場合はイベントログインボーナスの終了時刻
            var eventDailyBonusEndAt = MstMissionEventDataRepository
                .GetMstMissionEventDailyBonusScheduleModelFirstOrDefault(mstEventId)
                .EndAt;

            var remainingEventTimeSpan = (eventEndAt == DateTimeOffset.MaxValue || eventEndAt < nowTime)
                ? RemainingTimeSpan.Empty
                : new RemainingTimeSpan(eventEndAt - nowTime);
            
            var remainingDailyBonusTimeSpan = (eventDailyBonusEndAt == DateTimeOffset.MaxValue || eventDailyBonusEndAt < nowTime)
                ? RemainingTimeSpan.Empty
                : new RemainingTimeSpan(eventDailyBonusEndAt - nowTime);
            
            var remainingDailyNextUpdateTimeSpan = new RemainingTimeSpan(DailyResetTimeCalculator.GetRemainingTimeToDailyReset());
            return new EventMissionTimeUseCaseModel(
                remainingEventTimeSpan, 
                remainingDailyBonusTimeSpan,
                remainingDailyNextUpdateTimeSpan);
        }
    }
}
