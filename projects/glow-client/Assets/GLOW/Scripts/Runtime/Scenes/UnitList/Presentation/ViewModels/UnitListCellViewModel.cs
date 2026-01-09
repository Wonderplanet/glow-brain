using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.UnitList.Presentation.ViewModels
{
    public record UnitListCellViewModel(
        UserDataId UserUnitId,
        CharacterIconViewModel CharacterIcon,
        NotificationBadge NotificationBadge,
        UnitListSortType SortType);
}
