using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstArtworkAcquisitionRouteRepository
    {
        IReadOnlyList<MstArtworkAcquisitionRouteModel> GetArtworkAcquisitionRoutes();
        MstArtworkAcquisitionRouteModel GetArtworkAcquisitionRouteFirstOrDefault(MasterDataId mstArtworkId);
    }
}
