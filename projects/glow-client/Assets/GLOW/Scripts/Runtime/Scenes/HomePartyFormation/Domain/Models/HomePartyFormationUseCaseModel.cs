using System.Collections.Generic;
namespace GLOW.Scenes.HomePartyFormation.Domain.Models
{
    public record HomePartyFormationUseCaseModel(IReadOnlyList<HomePartyFormationUseCaseSpecialRuleUnitItemModel> UnitItems);
}
