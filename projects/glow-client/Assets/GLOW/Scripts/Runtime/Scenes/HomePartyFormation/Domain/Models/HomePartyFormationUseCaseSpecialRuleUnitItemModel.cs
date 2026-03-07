using GLOW.Core.Domain.ValueObjects;
namespace GLOW.Scenes.HomePartyFormation.Domain.Models
{
    public record HomePartyFormationUseCaseSpecialRuleUnitItemModel(
        UserDataId UserUnitId,
        InGameSpecialRuleAchievedFlag IsAchievedSpecialRule);
}
