using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserProfile.Domain.Models
{
    public record UserProfileModel(
        UserName Name,
        UserMyId MyId,
        UserProfileAvatarCellModel CurrentAvatarIcon,
        IReadOnlyList<UserProfileAvatarCellModel> AvatarIconList);
}
