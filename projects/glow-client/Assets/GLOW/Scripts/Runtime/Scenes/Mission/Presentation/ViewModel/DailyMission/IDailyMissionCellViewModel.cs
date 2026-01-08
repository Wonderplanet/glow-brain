using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.DailyMission
{
    public interface IDailyMissionCellViewModel
    {
        public MasterDataId DailyMissionId { get; }
        public MissionStatus MissionStatus { get; }
        public MissionProgress MissionProgress { get; }
        public CriterionCount CriterionCount { get; }
        public BonusPoint BonusPoint { get; }
        public MissionDescription MissionDescription { get; }
        public DestinationScene DestinationScene { get; }
    }
}