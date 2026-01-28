using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public class UserChangeEmblemResultDataTranslator
    {
        public static UserChangeEmblemResultModel Translate(UserChangeEmblemResultData data)
        {
            return new UserChangeEmblemResultModel(
                UserProfileDataTranslator.ToUserProfileModel(data.UsrProfile)
            );
        }
    }
}
