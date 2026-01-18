using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UserProfile.Domain.UseCases
{
    public class UpdateUserProfileAvatarBadgeUseCase
    {
        [Inject] IUserProfileBadgeRepository UserProfileBadgeRepository { get; }
        public List<MasterDataId> UpdateUserProfileAvatarBadge(List<MasterDataId> userProfileAvatarIds)
        {
            UserProfileBadgeRepository.DisplayedUserProfileAvatarIds = userProfileAvatarIds;

            return UserProfileBadgeRepository.DisplayedUserProfileAvatarIds;
        }
    }
}
