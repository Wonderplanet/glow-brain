using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.Evaluator
{
    public class InGamePartySpecialRuleEvaluator : IInGamePartySpecialRuleEvaluator
    {        
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }

        public ExistsPartySpecialRuleFlag ExistsPartySpecialRule(InGameContentType contentType, MasterDataId targetMstId)
        {
            if (targetMstId == MasterDataId.Empty) return ExistsPartySpecialRuleFlag.False;

            var existsMstInGameSpecialRule = MstInGameSpecialRuleDataRepository
                .GetInGameSpecialRuleModels(targetMstId, contentType)
                .Any(model => IsPartySpecialRule(model.RuleType));

            return new ExistsPartySpecialRuleFlag(existsMstInGameSpecialRule);
        }

        bool IsPartySpecialRule(RuleType ruleType)
        {
            return ruleType switch
            {
                RuleType.PartyUnitNum => true,
                RuleType.PartyRarity => true,
                RuleType.PartySeries => true,
                RuleType.PartyAttackRangeType => true,
                RuleType.PartyRoleType => true,
                RuleType.PartySummonCostUpperEqual => true,
                RuleType.PartySummonCostLowerEqual => true,
                _ => false
            };
        }
    }
}