using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.BeginnerMission.Domain.Model
{
    public record MissionBeginnerCellModel(
        MasterDataId MissionBeginnerId,
        MissionType MissionType,
        MissionStatus MissionStatus,
        MissionProgress MissionProgress,
        BeginnerMissionDayNumber BeginnerMissionDayNumber,
        CriterionValue CriterionValue,
        CriterionCount CriterionCount,
        BonusPoint BonusPoint,
        IReadOnlyList<PlayerResourceModel> PlayerResourceModels,
        MissionDescription MissionDescription,
        SortOrder SortOrder,
        DestinationScene DestinationScene)
    {
        public static MissionBeginnerCellModel Empty { get; } = new(
            MasterDataId.Empty,
            MissionType.Achievement,
            MissionStatus.Nothing,
            MissionProgress.Empty,
            BeginnerMissionDayNumber.Empty,
            CriterionValue.Empty,
            CriterionCount.Empty,
            BonusPoint.Empty,
            new List<PlayerResourceModel>(),
            MissionDescription.Empty,
            SortOrder.Empty,
            DestinationScene.Empty
        );
    };
}
