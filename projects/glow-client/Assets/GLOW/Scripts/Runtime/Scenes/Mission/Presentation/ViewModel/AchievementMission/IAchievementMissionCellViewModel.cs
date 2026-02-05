using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission
{
    public interface IAchievementMissionCellViewModel
    {
        public MasterDataId AchievementMissionId { get; }
        public MissionStatus MissionStatus { get; }
        public MissionProgress MissionProgress { get; }
        public CriterionValue CriterionValue { get; }
        public CriterionCount CriterionCount { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels { get; }
        public MissionDescription MissionDescription { get; }
        public DestinationScene DestinationScene { get; }
    }
}