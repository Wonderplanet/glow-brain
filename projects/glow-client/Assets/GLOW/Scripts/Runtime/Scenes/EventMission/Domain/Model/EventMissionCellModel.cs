using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.EventMission.Domain.Model
{
    public record EventMissionCellModel(
        MasterDataId EventMissionId,
        MasterDataId EventId,
        MissionType EventMissionType,
        MissionStatus MissionStatus,
        MissionProgress MissionProgress,
        CriterionCount CriterionCount,
        IReadOnlyList<PlayerResourceModel> PlayerResourceModels,
        MissionDescription MissionDescription,
        SortOrder SortOrder,
        DestinationScene DestinationScene)
    {
        public static EventMissionCellModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MissionType.Event,
            MissionStatus.Nothing,
            new MissionProgress(0),
            new CriterionCount(0),
            new List<PlayerResourceModel>(),
            new MissionDescription(""),
            new SortOrder(0),
            new DestinationScene(""));
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}