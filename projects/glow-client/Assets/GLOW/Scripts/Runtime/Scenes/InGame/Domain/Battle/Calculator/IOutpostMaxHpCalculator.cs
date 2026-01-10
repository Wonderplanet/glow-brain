using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public interface IOutpostMaxHpCalculator
    {
        OutpostMaxHpResult Calculate(
            OutpostEnhancementModel enhancement,
            HP artworkBonusHp,
            IReadOnlyList<MstInGameSpecialRuleModel> specialRules);
    }
}
