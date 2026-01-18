using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.DailyMission
{
    public class DailyMissionCellViewModel : IDailyMissionCellViewModel
    {
        public MasterDataId DailyMissionId { get; }
        public MissionStatus MissionStatus { get; }
        public MissionProgress MissionProgress { get; }
        public CriterionCount CriterionCount { get; }
        public BonusPoint BonusPoint { get; }
        public MissionDescription MissionDescription { get; }
        public DestinationScene DestinationScene { get; }
        
        public DailyMissionCellViewModel(MasterDataId dailyMissionId, MissionStatus missionStatus, MissionProgress missionProgress, CriterionCount criterionCount, BonusPoint bonusPoint, MissionDescription missionDescription, DestinationScene destinationScene)
        {
            DailyMissionId = dailyMissionId;
            MissionStatus = missionStatus;
            MissionProgress = missionProgress;
            CriterionCount = criterionCount;
            BonusPoint = bonusPoint;
            MissionDescription = missionDescription;
            DestinationScene = destinationScene;
        }
    }
}