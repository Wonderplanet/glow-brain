using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstArtworkDataRepository
    {
        IReadOnlyList<MstArtworkModel> GetArtworks();
        IReadOnlyList<MstArtworkModel> GetSeriesArtwork(MasterDataId mstSeriesId);
        MstArtworkModel GetArtwork(MasterDataId key);
    }
}
