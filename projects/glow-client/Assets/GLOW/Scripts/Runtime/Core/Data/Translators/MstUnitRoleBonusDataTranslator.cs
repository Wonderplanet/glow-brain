using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Data.Translators
{
    public class MstUnitRoleBonusDataTranslator
    {
        public static MstUnitRoleBonusModel Translate(MstUnitRoleBonusData roleBonusData)
        {
            return new MstUnitRoleBonusModel(
                new MasterDataId(roleBonusData.Id),
                roleBonusData.RoleType,
                new CharacterColorAdvantageAttackBonus(roleBonusData.ColorAdvantageAttackBonus),
                new CharacterColorAdvantageDefenseBonus(roleBonusData.ColorAdvantageDefenseBonus));
        }
    }
}
