using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public class OutpostMaxHpCalculator : IOutpostMaxHpCalculator
    {
        public OutpostMaxHpResult Calculate(
            OutpostEnhancementModel enhancement,
            HP artworkBonusHp,
            IReadOnlyList<MstInGameSpecialRuleModel> specialRules)
        {
            var ruleModel = specialRules.FirstOrDefault(
                rule => rule.RuleType == RuleType.OutpostHp,
                MstInGameSpecialRuleModel.Empty);

            if (!ruleModel.IsEmpty())
            {
                var overrideHp = ruleModel.RuleValue.ToStartOutpostHp().ToHp(); 
                return new OutpostMaxHpResult(overrideHp, OutpostHpSpecialRuleFlag.True);
            }

            var defaultHp = OutpostDefaultParameterConst.DefaultOutpostHp;
            var hpEnhancementValue = enhancement.GetEnhancementValue(OutpostEnhancementType.OutpostHP);
            var maxHp = new HP(defaultHp + (int)hpEnhancementValue.Value + artworkBonusHp.Value);
            
            return new OutpostMaxHpResult(maxHp, OutpostHpSpecialRuleFlag.False);
        }
    }
}
