using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Data.Translators
{
    public class MstSpecialRoleLevelUpAttackElementDataTranslator
    {
        static public IReadOnlyList<SpecialRoleLevelUpAttackElement> ToSpecialRoleLevelUpAttackElements(
            IReadOnlyList<MstSpecialRoleLevelUpAttackElementData> mstSpecialRoleLevelUpAttackElements)
        {
            return mstSpecialRoleLevelUpAttackElements
                .Select(mstLevelUpAttackElement => new SpecialRoleLevelUpAttackElement(
                    new MasterDataId(mstLevelUpAttackElement.Id),
                    new MasterDataId(mstLevelUpAttackElement.MstAttackElementId),
                    new AttackPowerParameterValue(mstLevelUpAttackElement.MinPowerParameter),
                    new AttackPowerParameterValue(mstLevelUpAttackElement.MaxPowerParameter),
                    new EffectiveCount(mstLevelUpAttackElement.MinEffectiveCount),
                    new EffectiveCount(mstLevelUpAttackElement.MaxEffectiveCount),
                    new TickCount(mstLevelUpAttackElement.MinEffectiveDuration),
                    new TickCount(mstLevelUpAttackElement.MaxEffectiveDuration),
                    new StateEffectParameter(mstLevelUpAttackElement.MinEffectParameter),
                    new StateEffectParameter(mstLevelUpAttackElement.MaxEffectParameter)))
                .ToList();
        }
    }
}