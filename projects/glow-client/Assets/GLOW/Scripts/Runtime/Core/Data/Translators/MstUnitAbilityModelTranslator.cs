using Cysharp.Text;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstUnitAbilityModelTranslator
    {
        public static MstUnitAbilityModel Translate(
            MstUnitAbilityData mstUnitAbilityData,
            MstAbilityData mstAbilityData,
            MstAbilityI18nData mstAbilityI18nData,
            UnitRank unlockUnitRank)
        {
            if (mstUnitAbilityData == null || mstAbilityData == null || mstAbilityI18nData == null)
            {
                return MstUnitAbilityModel.Empty;
            }

            var description = ZString.Format(
                mstAbilityI18nData.Description,
                mstUnitAbilityData.AbilityParameter1,
                mstUnitAbilityData.AbilityParameter2);

            var unitAbility = new UnitAbility(
                mstAbilityData.AbilityType,
                new UnitAbilityAssetKey(mstAbilityData.AssetKey),
                new UnitAbilityParameter(mstUnitAbilityData.AbilityParameter1),
                new UnitAbilityParameter(mstUnitAbilityData.AbilityParameter2),
                new UnitAbilityParameter(mstUnitAbilityData.AbilityParameter3),
                description,
                unlockUnitRank);

            return new MstUnitAbilityModel(
                new MasterDataId(mstUnitAbilityData.Id),
                unitAbility);
        }
    }
}
