using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IInGameSpecialRuleUnitStatusProvider
    {
        (PercentageM specialRuleHpPercentageM, PercentageM specialRuleAttackPercentageM)
            GetSpecialRuleUnitStatus(
                MasterDataId mstUnitId,
                IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);
    }
}
