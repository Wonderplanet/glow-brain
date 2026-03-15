using System.Collections.Generic;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstArtworkAcquisitionRouteModel(
        MasterDataId MstArtworkId,
        IReadOnlyList<ArtworkAcquisitionRoute> AcquisitionRoutes)
    {
        public static MstArtworkAcquisitionRouteModel Empty { get; } =
            new(MasterDataId.Empty, new List<ArtworkAcquisitionRoute>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
