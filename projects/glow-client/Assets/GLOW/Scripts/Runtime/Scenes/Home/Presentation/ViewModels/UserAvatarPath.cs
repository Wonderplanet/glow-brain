using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record UserAvatarPath(string Value)
    {
        public static UserAvatarPath Empty { get; } = new(string.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public static UserAvatarPath FromUnitAssetKey(UnitAssetKey unitAssetKey)
        {
            if (unitAssetKey.IsEmpty()) return UserAvatarPath.Empty;
            
            var avatarPath = AvatarAssetPath.GetAvatarIconPath(unitAssetKey.Value);
            return new UserAvatarPath(avatarPath);
        }
    }
}