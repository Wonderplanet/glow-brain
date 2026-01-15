using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Data.Translators
{
    public class MstShopPassEffectDataTranslator
    {
        public static MstShopPassEffectModel ToMstShopPassEffectModel(MstShopPassEffectData data)
        {
            return new MstShopPassEffectModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstShopPassId),
                data.EffectType,
                new PassEffectValue(data.EffectValue));
        }
    }
}