using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Scenes.BoxGachaLineupDialog.Domain.ValueObject;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.ViewModel
{
    public record BoxGachaLineupDialogViewModel(
        BoxResetCount BoxResetCount,
        BoxGachaLineupListViewModel URBoxGachaLineupListViewModel,
        BoxGachaLineupListViewModel SSRBoxGachaLineupListViewModel,
        BoxGachaLineupListViewModel SRBoxGachaLineupListViewModel,
        BoxGachaLineupListViewModel RBoxGachaLineupListViewModel,
        UnitContainInLineupFlag IsUnitContainInLineup)
    {
        public static BoxGachaLineupDialogViewModel Empty { get; } = new BoxGachaLineupDialogViewModel(
            BoxResetCount.Empty,
            BoxGachaLineupListViewModel.Empty,
            BoxGachaLineupListViewModel.Empty,
            BoxGachaLineupListViewModel.Empty,
            BoxGachaLineupListViewModel.Empty,
            UnitContainInLineupFlag.False
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}