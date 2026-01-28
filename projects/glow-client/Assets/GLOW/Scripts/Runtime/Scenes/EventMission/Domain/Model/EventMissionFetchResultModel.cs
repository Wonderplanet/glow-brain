using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EventMission.Domain.Model
{
    public record EventMissionFetchResultModel(
        MasterDataId MstEventIdForTimeInformation,
        EventMissionAchievementResultModel AchievementResultModel,
        EventMissionDailyBonusResultModel DailyBonusResultModel)
    {
        public static EventMissionFetchResultModel Empty { get; } = new EventMissionFetchResultModel(
            MasterDataId.Empty,
            EventMissionAchievementResultModel.Empty,
            EventMissionDailyBonusResultModel.Empty
        );
    }
}
