using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IOutpostArtworkBadgeRepository
    {
        List<MasterDataId> DisplayedOutpostArtworkIds { get; set; }
    }
}
