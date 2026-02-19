using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class InGameSpecialRuleUnitStatusDataTranslator
    {
        public static MstInGameSpecialRuleUnitStatusModel ToInGameSpecialRuleUnitStatusModel(MstInGameSpecialRuleUnitStatusData data)
        {
            return new MstInGameSpecialRuleUnitStatusModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.GroupId),
                data.TargetType,
                new SpecialRuleUnitStatusTargetValue(data.TargetValue),
                data.StatusParameterType,
                new SpecialRuleUnitStatusEffectValue(data.EffectValue));
        }
    }
}
