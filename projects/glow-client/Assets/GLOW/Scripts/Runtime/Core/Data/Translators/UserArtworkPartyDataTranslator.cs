using System;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UserArtworkPartyDataTranslator
    {
        public static UserArtworkPartyModel ToModel(UsrArtworkPartyData data)
        {
            return new UserArtworkPartyModel(
                data.MstArtworkId1 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId1),
                data.MstArtworkId2 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId2),
                data.MstArtworkId3 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId3),
                data.MstArtworkId4 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId4),
                data.MstArtworkId5 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId5),
                data.MstArtworkId6 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId6),
                data.MstArtworkId7 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId7),
                data.MstArtworkId8 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId8),
                data.MstArtworkId9 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId9),
                data.MstArtworkId10 == String.Empty ? MasterDataId.Empty : new MasterDataId(data.MstArtworkId10));
        }
    }
}
