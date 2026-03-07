using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Scenes.BoxGachaLineupDialog.Domain.ValueObject;

namespace GLOW.Scenes.BoxGachaLineupDialog.Domain.Model
{
    public record BoxGachaLineupModel(
        BoxResetCount BoxResetCount,
        BoxGachaLineupListModel BoxGachaURLineupListViewModel,
        BoxGachaLineupListModel BoxGachaSSRLineupListViewModel,
        BoxGachaLineupListModel BoxGachaSRLineupListViewModel,
        BoxGachaLineupListModel BoxGachaRLineupListViewModel,
        UnitContainInLineupFlag IsUnitContainInLineup)
    {
        public static BoxGachaLineupModel Empty { get; } = new BoxGachaLineupModel(
            BoxResetCount.Empty,
            BoxGachaLineupListModel.Empty,
            BoxGachaLineupListModel.Empty,
            BoxGachaLineupListModel.Empty,
            BoxGachaLineupListModel.Empty,
            UnitContainInLineupFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}