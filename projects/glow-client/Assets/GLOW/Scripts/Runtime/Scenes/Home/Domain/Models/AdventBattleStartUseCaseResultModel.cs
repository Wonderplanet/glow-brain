using GLOW.Scenes.Home.Domain.Constants;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record AdventBattleStartUseCaseResultModel(
        AdventBattleErrorType ErrorType,
        InGameSpecialRuleStatusModel SpecialRuleStatusModel);
}
