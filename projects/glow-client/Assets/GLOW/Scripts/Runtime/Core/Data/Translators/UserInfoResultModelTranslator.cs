using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UserInfoResultModelTranslator
    {
        public static UserInfoResultModel ToUserInfoResultModel(UserInfoResultData data)
        {
            if(data == null) return UserInfoResultModel.Empty;
            
            return new UserInfoResultModel(new UserMyId(data.MyId));
        }
    }
}