using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.PartyFormation.Presentation.ViewModels
{
    public record PartyFormationUnitListCellViewModel(
        UserDataId UserUnitId,
        CharacterIconViewModel CharacterIconViewModel,
        PartyFormationAssignFlag IsAssigned,
        PartyFormationUnitSelectableFlag IsSelectable,
        UnitListSortType SortType,
        NotificationBadge NotificationBadge,
        EventBonusPercentage EventBonusPercentage,
        InGameSpecialRuleAchievedFlag IsAchievedSpecialRule,
        InGameSpecialRuleUnitStatusTargetFlag IsInGameSpecialRuleUnitStatusTarget
        );
}
