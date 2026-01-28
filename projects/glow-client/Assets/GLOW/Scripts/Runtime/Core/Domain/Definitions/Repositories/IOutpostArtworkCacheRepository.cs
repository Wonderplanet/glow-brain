using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IOutpostArtworkCacheRepository
    {
        void SetArtworkList(
            IReadOnlyList<MasterDataId> displayedMstArtworkIds,
            MasterDataId selectedMstArtworkId);

        void SetSelectedArtwork(MasterDataId selectedMstArtworkId);

        IReadOnlyList<MasterDataId> GetDisplayedArtworkList();
        MasterDataId GetSelectedArtwork();
    }
}
