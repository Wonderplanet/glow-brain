using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.WeeklyMission
{
    public class WeeklyMissionCellViewModel : IWeeklyMissionCellViewModel
    {
        public MasterDataId WeeklyMissionId { get; }
        public MissionStatus MissionStatus { get; }
        public MissionProgress MissionProgress { get; }
        public CriterionCount CriterionCount { get; }
        public BonusPoint BonusPoint { get; }
        public MissionDescription MissionDescription { get; }
        public DestinationScene DestinationScene { get; }
        
        public WeeklyMissionCellViewModel(MasterDataId weeklyMissionId, MissionStatus missionStatus, MissionProgress missionProgress, CriterionCount criterionCount, BonusPoint bonusPoint, MissionDescription missionDescription, DestinationScene destinationScene)
        {
            WeeklyMissionId = weeklyMissionId;
            MissionStatus = missionStatus;
            MissionProgress = missionProgress;
            CriterionCount = criterionCount;
            BonusPoint = bonusPoint;
            MissionDescription = missionDescription;
            DestinationScene = destinationScene;
        }
    }
}