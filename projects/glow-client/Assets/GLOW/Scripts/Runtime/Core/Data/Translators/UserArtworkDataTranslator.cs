using GLOW.Core.Data.Data;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public static class UserArtworkDataTranslator
    {
        public static UserArtworkModel ToUserArtworkModel(UsrArtworkData data)
        {
            return new UserArtworkModel(
                new MasterDataId(data.MstArtworkId),
                new NewEncyclopediaFlag(data.IsNewEncyclopedia));
        }
    }
}
