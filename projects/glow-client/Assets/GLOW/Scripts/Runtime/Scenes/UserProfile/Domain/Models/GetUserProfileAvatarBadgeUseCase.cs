using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UserProfile.Domain.Models
{
    public class GetUserProfileAvatarBadgeUseCase
    {
        [Inject] IUserProfileBadgeRepository UserProfileBadgeRepository { get; }
        public List<MasterDataId> GetUserProfileAvatarBadge()
        {
            return UserProfileBadgeRepository.DisplayedUserProfileAvatarIds;
        }
    }
}
