using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record UnitListCellModel(
        UserDataId UserUnitId,
        CharacterIconModel CharacterIconModel,
        NotificationBadge NotificationBadge,
        UnitListSortType SortType);
}
