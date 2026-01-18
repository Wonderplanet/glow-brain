using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.PartyFormation.Domain.Models
{
    public record PartyFormationUnitListCellModel(
        UserDataId UserUnitId,
        CharacterIconModel CharacterIcon,
        PartyFormationAssignFlag IsAssigned,
        PartyFormationUnitSelectableFlag IsSelectable,
        UnitListSortType SortType,
        NotificationBadge NotificationBadge,
        EventBonusPercentage EventBonusPercentage,
        InGameSpecialRuleUnitStatusTargetFlag InGameSpecialRuleUnitStatusTargetFlag);
}
