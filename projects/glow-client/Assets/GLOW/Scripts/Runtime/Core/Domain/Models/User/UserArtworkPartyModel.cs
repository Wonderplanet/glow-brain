using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserArtworkPartyModel(
        MasterDataId MstArtworkId1,
        MasterDataId MstArtworkId2,
        MasterDataId MstArtworkId3,
        MasterDataId MstArtworkId4,
        MasterDataId MstArtworkId5,
        MasterDataId MstArtworkId6,
        MasterDataId MstArtworkId7,
        MasterDataId MstArtworkId8,
        MasterDataId MstArtworkId9,
        MasterDataId MstArtworkId10)
    {
        public static UserArtworkPartyModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public IReadOnlyList<MasterDataId> GetArtworkList()
        {
            return new List<MasterDataId>
            {
                MstArtworkId1,
                MstArtworkId2,
                MstArtworkId3,
                MstArtworkId4,
                MstArtworkId5,
                MstArtworkId6,
                MstArtworkId7,
                MstArtworkId8,
                MstArtworkId9,
                MstArtworkId10
            };
        }
    }
}
