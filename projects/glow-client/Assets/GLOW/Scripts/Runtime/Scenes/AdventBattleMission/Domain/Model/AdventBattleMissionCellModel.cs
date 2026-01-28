using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.AdventBattleMission.Domain.Model
{
    public record AdventBattleMissionCellModel(
        MasterDataId AdventBattleMissionId,
        MissionType MissionType,
        MissionCategory MissionCategory,
        MissionStatus MissionStatus,
        MissionProgress MissionProgress,
        CriterionCount CriterionCount,
        IReadOnlyList<PlayerResourceModel> PlayerResourceModels,
        MissionDescription MissionDescription,
        SortOrder SortOrder,
        DestinationScene DestinationScene,
        RemainingTimeSpan EndTime)
    {
        public static AdventBattleMissionCellModel Empty { get; } = new(
            MasterDataId.Empty,
            MissionType.Event,
            MissionCategory.AdventBattle,
            MissionStatus.Nothing,
            MissionProgress.Empty,
            CriterionCount.Empty,
            new List<PlayerResourceModel>(),
            MissionDescription.Empty,
            SortOrder.Empty,
            DestinationScene.Empty,
            RemainingTimeSpan.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}