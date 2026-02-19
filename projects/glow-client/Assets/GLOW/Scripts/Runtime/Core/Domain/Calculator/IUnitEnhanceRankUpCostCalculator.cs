using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.Models;

namespace GLOW.Core.Domain.Calculator
{
    public interface IUnitEnhanceRankUpCostCalculator
    {
        IReadOnlyList<UnitEnhanceCostItemModel> CalculateRankUpCosts(MstCharacterModel mstUnit, UserUnitModel userUnit);

        IReadOnlyList<UnitEnhanceCostItemModel> CalculateRankUpCostsForLevel(MstCharacterModel mstUnit, UnitLevel unitLevel);
    }
}
