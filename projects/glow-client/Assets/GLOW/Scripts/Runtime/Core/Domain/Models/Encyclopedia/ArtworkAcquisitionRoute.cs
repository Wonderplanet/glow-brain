using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Encyclopedia
{
    public record ArtworkAcquisitionRoute(
        MasterDataId MstId,
        ArtworkAcquisitionRouteType Type,
        MasterDataId AcquisitionId);
}
