using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Domain.Model.AchievementMission
{
    public record MissionAchievementCellModel(
        MasterDataId MissionAchievementId,
        MissionType MissionType,
        MissionStatus MissionStatus,
        MissionProgress MissionProgress,
        CriterionValue CriterionValue,
        CriterionCount CriterionCount,
        IReadOnlyList<PlayerResourceModel> PlayerResourceModels,
        MissionDescription MissionDescription,
        SortOrder SortOrder,
        DestinationScene DestinationScene)
    {
        public static MissionAchievementCellModel Empty { get; } = new(
            MasterDataId.Empty,
            MissionType.Achievement,
            MissionStatus.Nothing,
            MissionProgress.Empty, 
            CriterionValue.Empty, 
            CriterionCount.Empty, 
            new List<PlayerResourceModel>(),
            MissionDescription.Empty, 
            SortOrder.Empty, 
            DestinationScene.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}