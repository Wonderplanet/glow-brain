using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Helper
{
    public interface IUnitStatusCalculateHelper
    {
        UnitCalculateStatusModel Calculate(
            MstCharacterModel mstUnit,
            UnitLevel unitLevel,
            UnitRank unitRank,
            UnitGrade unitGrade);

        UnitCalculateStatusModel CalculateStatusWithSpecialRule(
            UnitCalculateStatusModel calculatedStatus,
            MasterDataId unitId,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);
    }
}
