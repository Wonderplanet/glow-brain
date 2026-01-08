using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission
{
    public class AchievementMissionCellViewModel : IAchievementMissionCellViewModel
    {
        public MasterDataId AchievementMissionId { get; }
        public MissionStatus MissionStatus { get; }
        public MissionProgress MissionProgress { get; }
        public CriterionValue CriterionValue { get; }
        public CriterionCount CriterionCount { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels { get; }
        public MissionDescription MissionDescription { get; }
        public DestinationScene DestinationScene { get; }
        
        public AchievementMissionCellViewModel(
            MasterDataId achievementMissionId, 
            MissionStatus missionStatus, 
            MissionProgress missionProgress,
            CriterionValue criterionValue,
            CriterionCount criterionCount, 
            IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels, 
            MissionDescription missionDescription, 
            DestinationScene destinationScene)
        {
            AchievementMissionId = achievementMissionId;
            MissionStatus = missionStatus;
            MissionProgress = missionProgress;
            CriterionValue = criterionValue;
            CriterionCount = criterionCount;
            PlayerResourceIconViewModels = playerResourceIconViewModels;
            MissionDescription = missionDescription;
            DestinationScene = destinationScene;
        }
    }
}