using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserProfile.Domain.Models
{
    public record UserProfileAvatarCellModel(
        MasterDataId Id,
        CharacterIconAssetPath AvatarIconAssetPath,
        NotificationBadge Badge);
}
