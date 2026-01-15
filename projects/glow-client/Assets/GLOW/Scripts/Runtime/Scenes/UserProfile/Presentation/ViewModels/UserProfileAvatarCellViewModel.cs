using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserProfile.Presentation.ViewModels
{
    public record UserProfileAvatarCellViewModel(
        MasterDataId Id,
        CharacterIconAssetPath AvatarIconAssetPath,
        NotificationBadge Badge);
}
