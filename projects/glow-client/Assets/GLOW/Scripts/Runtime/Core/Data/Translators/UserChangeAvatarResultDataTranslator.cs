using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public class UserChangeAvatarResultDataTranslator
    {
        public static UserChangeAvatarResultModel Translate(UserChangeAvatarResultData data)
        {
            return new UserChangeAvatarResultModel(
                UserProfileDataTranslator.ToUserProfileModel(data.UsrProfile)
            );
        }
    }
}
