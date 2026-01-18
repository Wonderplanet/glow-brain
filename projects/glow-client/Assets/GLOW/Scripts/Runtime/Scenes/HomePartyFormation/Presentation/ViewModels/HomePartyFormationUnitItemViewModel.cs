using GLOW.Core.Domain.ValueObjects;
namespace GLOW.Scenes.HomePartyFormation.Presentation.Presenters
{
    public record HomePartyFormationUnitItemViewModel(
        UserDataId UserUnitId,
        InGameSpecialRuleAchievedFlag IsSpecialRuleAchieve);
}

