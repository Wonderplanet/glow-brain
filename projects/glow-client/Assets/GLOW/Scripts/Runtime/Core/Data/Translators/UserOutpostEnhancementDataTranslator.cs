using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Core.Data.Translators
{
    public class UserOutpostEnhancementDataTranslator
    {
        public static UserOutpostEnhanceModel TranslateToModel(UsrOutpostEnhancementData data)
        {
            return new UserOutpostEnhanceModel(
                new MasterDataId(data.MstOutpostId),
                new MasterDataId(data.MstOutpostEnhancementId),
                new OutpostEnhanceLevel(data.Level));
        }
    }
}
