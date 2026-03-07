using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UserProfileDataTranslator
    {
        public static UserProfileModel ToUserProfileModel(UsrProfileData userProfileData)
        {
            return new UserProfileModel(
                !string.IsNullOrEmpty(userProfileData.Name) ? new UserName(userProfileData.Name) : UserName.Empty,
                !string.IsNullOrEmpty(userProfileData.MstUnitId)
                    ? new MasterDataId(userProfileData.MstUnitId)
                    : MasterDataId.Empty,
                !string.IsNullOrEmpty(userProfileData.MstEmblemId)
                    ? new MasterDataId(userProfileData.MstEmblemId)
                    : MasterDataId.Empty,
                userProfileData.NameUpdateAt,
                !string.IsNullOrEmpty(userProfileData.MstAvatarId)
                    ? new MasterDataId(userProfileData.MstAvatarId)
                    : MasterDataId.Empty,
                !string.IsNullOrEmpty(userProfileData.MstAvatarFrameId)
                    ? new MasterDataId(userProfileData.MstAvatarFrameId)
                    : MasterDataId.Empty,
                !string.IsNullOrEmpty(userProfileData.MyId)
                    ? new UserMyId(userProfileData.MyId)
                    : UserMyId.Empty);
        }
    }
}
