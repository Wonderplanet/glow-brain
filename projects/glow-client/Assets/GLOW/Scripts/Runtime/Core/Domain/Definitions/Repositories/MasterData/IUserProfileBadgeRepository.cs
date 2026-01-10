using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IUserProfileBadgeRepository
    {
        List<MasterDataId> DisplayedUserProfileAvatarIds { get; set; }
    }
}
