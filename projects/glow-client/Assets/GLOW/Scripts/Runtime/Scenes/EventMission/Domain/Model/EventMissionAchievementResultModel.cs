using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.EventMission.Domain.Model
{
    public record EventMissionAchievementResultModel(
        IReadOnlyList<EventMissionCellModel> OpeningEventAchievementCellModels,
        IReadOnlyList<MstEventModel> OpeningMstEventModels)
    {
        public static EventMissionAchievementResultModel Empty { get; } = new EventMissionAchievementResultModel(
            new List<EventMissionCellModel>(),
            new List<MstEventModel>()
        );
    }

}
