using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattleMission.Presentation.ViewModel
{
    public record AdventBattleMissionCellViewModel(
        MasterDataId AdventBattleMissionId,
        MissionType MissionType,
        MissionStatus MissionStatus,
        MissionProgress MissionProgress,
        CriterionCount CriterionCount,
        IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels,
        MissionDescription MissionDescription,
        DestinationScene DestinationScene,
        RemainingTimeSpan EndTimeSpan)
    {
        public static AdventBattleMissionCellViewModel Empty { get; } = new AdventBattleMissionCellViewModel(
            MasterDataId.Empty,
            MissionType.LimitedTerm,
            MissionStatus.Nothing,
            MissionProgress.Empty,
            CriterionCount.Empty,
            new List<PlayerResourceIconViewModel>(),
            MissionDescription.Empty,
            DestinationScene.Empty,
            RemainingTimeSpan.Empty);
    }
}