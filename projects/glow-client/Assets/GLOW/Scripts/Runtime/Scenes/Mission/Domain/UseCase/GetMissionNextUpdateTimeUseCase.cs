using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Mission.Domain.UseCase
{
    public class GetMissionNextUpdateTimeUseCase
    {
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }

        public RemainingTimeSpan GetNextUpdateTime(MissionType missionType)
        {
            return new RemainingTimeSpan(GetRemainingTime(missionType));
        }

        TimeSpan GetRemainingTime(MissionType missionType)
        {
            return missionType switch
            {
                MissionType.Daily => DailyResetTimeCalculator.GetRemainingTimeToDailyReset(),
                MissionType.DailyBonus => DailyResetTimeCalculator.GetRemainingTimeToDailyReset(),
                MissionType.Weekly => DailyResetTimeCalculator.GetRemainingTimeToWeeklyReset(),
                _ =>  TimeSpan.Zero
            };
        }
    }
}
