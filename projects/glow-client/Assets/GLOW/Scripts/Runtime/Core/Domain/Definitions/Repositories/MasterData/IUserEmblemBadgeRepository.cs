using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IUserEmblemBadgeRepository
    {
        List<MasterDataId> DisplayedUserEmblemIds { get; set; }
    }
}
