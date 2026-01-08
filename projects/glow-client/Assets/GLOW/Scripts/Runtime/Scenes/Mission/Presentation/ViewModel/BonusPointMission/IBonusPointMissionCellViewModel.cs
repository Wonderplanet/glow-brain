using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission
{
    public interface IBonusPointMissionCellViewModel
    {
        public MasterDataId BonusPointMissionId { get; }
        public MissionStatus MissionStatus { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels { get; }
        public CriterionCount CriterionCount { get; }
    }
}