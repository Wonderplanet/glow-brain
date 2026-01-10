using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PartyFormation.Domain.Evaluator
{
    public interface IInGamePartySpecialRuleEvaluator
    {
        ExistsPartySpecialRuleFlag ExistsPartySpecialRule(InGameContentType contentType, MasterDataId targetMstId);
    }
}