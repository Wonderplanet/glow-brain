using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserProfile.Presentation.ViewModels
{
    public record UserProfileViewModel(
        UserName Name,
        UserMyId MyId,
        UserProfileAvatarCellViewModel CurrentAvatarIcon,
        IReadOnlyList<UserProfileAvatarCellViewModel> AvatarIconList);
}
