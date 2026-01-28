using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.AdventBattleMission.Presentation.ViewModel
{
    public record AdventBattleMissionViewModel(
        IReadOnlyList<AdventBattleMissionCellViewModel> AdventBattleMissionCellViewModels,
        MissionBulkReceivableFlag IsBulkReceivable)
    {
        public static AdventBattleMissionViewModel Empty { get; } = new(
            new List<AdventBattleMissionCellViewModel>(),
            MissionBulkReceivableFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}