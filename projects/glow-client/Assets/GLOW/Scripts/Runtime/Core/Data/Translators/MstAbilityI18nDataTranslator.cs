using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstAbilityI18nDataTranslator
    {
        public static MstAbilityDescriptionModel Translate(MstAbilityData abilityData, MstAbilityI18nData mstAbilityI18NData)
        {
            return new MstAbilityDescriptionModel(
                new MasterDataId(abilityData.Id),
                abilityData.AbilityType,
                mstAbilityI18NData.Description,
                new AbilityFilterTitle(mstAbilityI18NData.FilterTitle));
        }
    }
}
