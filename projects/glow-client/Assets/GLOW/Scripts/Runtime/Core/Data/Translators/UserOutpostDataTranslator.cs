using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UserOutpostDataTranslator
    {
        public static UserHomeOutpostModel TranslateToModel(UsrOutpostData data)
        {
            var mstArtworkId = string.IsNullOrEmpty(data.MstArtworkId)
                ? MasterDataId.Empty
                : new MasterDataId(data.MstArtworkId);

            return new UserHomeOutpostModel(
                new MasterDataId(data.MstOutpostId),
                mstArtworkId,
                data.IsUsed ? UserOutpostUsingFlag.True : UserOutpostUsingFlag.False);
        }
    }
}
