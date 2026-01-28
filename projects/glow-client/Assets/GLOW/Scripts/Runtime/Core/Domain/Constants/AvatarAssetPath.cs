using Cysharp.Text;

namespace GLOW.Core.Domain.Constants
{
    public static class AvatarAssetPath
    {
        public static string GetAvatarIconPath(string key)
        {
            return ZString.Format("unit_icon_{0}", key);
        }
        public static string GetAvatarFrameIconPath(string key)
        {
            return ZString.Format("character_icon_frame_{0}", key);
        }
    }
}
