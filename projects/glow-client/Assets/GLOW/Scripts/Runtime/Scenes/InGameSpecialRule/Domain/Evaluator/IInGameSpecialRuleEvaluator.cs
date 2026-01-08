using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGameSpecialRule.Domain.Evaluator
{
    public interface IInGameSpecialRuleEvaluator
    {
        ExistsSpecialRuleFlag ExistsSpecialRule(InGameContentType contentType, MasterDataId targetMstId, QuestType questType);
    }
}
