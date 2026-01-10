using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Repositories
{
    public class OutpostArtworkCacheRepository : IOutpostArtworkCacheRepository
    {
        IReadOnlyList<MasterDataId> _displayedMstArtworkIds;
        MasterDataId _selectedMstArtworkId;

        public void SetArtworkList(
            IReadOnlyList<MasterDataId> displayedMstArtworkIds,
            MasterDataId selectedMstArtworkId)
        {
            _displayedMstArtworkIds = displayedMstArtworkIds;
            _selectedMstArtworkId = selectedMstArtworkId;
        }

        public void SetSelectedArtwork(MasterDataId selectedMstArtworkId)
        {
            _selectedMstArtworkId = selectedMstArtworkId;
        }

        public IReadOnlyList<MasterDataId> GetDisplayedArtworkList()
        {
            return _displayedMstArtworkIds;
        }

        public MasterDataId GetSelectedArtwork()
        {
            return _selectedMstArtworkId;
        }
    }
}
