using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionCell
{
    public class EventMissionCellViewModel : IEventMissionCellViewModel
    {
        public MasterDataId EventMissionId { get; }
        public MasterDataId EventId { get; }
        public MissionStatus MissionStatus { get; }
        public MissionProgress MissionProgress { get; }
        public CriterionCount CriterionCount { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels { get; }
        public MissionDescription MissionDescription { get; }
        public DestinationScene DestinationScene { get; }
        
        public EventMissionCellViewModel(MasterDataId eventMissionId, MasterDataId eventId, MissionStatus missionStatus, MissionProgress missionProgress, CriterionCount criterionCount, IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels, MissionDescription missionDescription, DestinationScene destinationScene)
        {
            EventMissionId = eventMissionId;
            EventId = eventId;
            MissionStatus = missionStatus;
            MissionProgress = missionProgress;
            CriterionCount = criterionCount;
            PlayerResourceIconViewModels = playerResourceIconViewModels;
            MissionDescription = missionDescription;
            DestinationScene = destinationScene;
        }
    }
}