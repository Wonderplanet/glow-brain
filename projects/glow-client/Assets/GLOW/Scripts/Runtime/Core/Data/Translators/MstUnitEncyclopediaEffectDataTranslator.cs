using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstUnitEncyclopediaEffectDataTranslator
    {
        public static MstUnitEncyclopediaEffectModel ToUnitEncyclopediaEffectModel(MstUnitEncyclopediaEffectData data)
        {
            return new MstUnitEncyclopediaEffectModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstUnitEncyclopediaRewardId),
                data.EffectType,
                new UnitEncyclopediaEffectValue((decimal)data.Value)
            );
        }
    }
}
