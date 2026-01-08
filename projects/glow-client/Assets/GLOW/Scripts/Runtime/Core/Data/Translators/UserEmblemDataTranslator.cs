using GLOW.Core.Data.Data;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public static class UserEmblemDataTranslator
    {
        public static UserEmblemModel ToUserEmblemModel(UsrEmblemData data)
        {
            if(null == data) return UserEmblemModel.Empty;

            return new UserEmblemModel(
                new MasterDataId(data.MstEmblemId),
                new NewEncyclopediaFlag(data.IsNewEncyclopedia));
        }
    }
}
