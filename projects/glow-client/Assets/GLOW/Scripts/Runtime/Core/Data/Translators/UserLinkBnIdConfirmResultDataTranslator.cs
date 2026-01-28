using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UserLinkBnIdConfirmResultDataTranslator
    {
        public static UserLinkBnIdConfirmResultModel Translate(UserLinkBnidConfirmResultData data)
        {
            return new UserLinkBnIdConfirmResultModel(
                !string.IsNullOrEmpty(data.Name) ? new UserName(data.Name) : UserName.Empty,
                data.Level != null ? new UserLevel((int)data.Level) : UserLevel.Empty,
                !string.IsNullOrEmpty(data.MyId) ? new UserMyId(data.MyId) : UserMyId.Empty
            );
        }
    }
}
