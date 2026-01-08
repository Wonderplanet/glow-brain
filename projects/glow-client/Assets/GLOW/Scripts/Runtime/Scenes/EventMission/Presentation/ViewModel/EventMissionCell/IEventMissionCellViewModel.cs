using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionCell
{
    public interface IEventMissionCellViewModel
    {
        public MasterDataId EventMissionId { get; }
        public MasterDataId EventId { get; }
        public MissionStatus MissionStatus { get; }
        public MissionProgress MissionProgress { get; }
        public CriterionCount CriterionCount { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels { get; }
        public MissionDescription MissionDescription { get; }
        public DestinationScene DestinationScene { get; }
    }
}