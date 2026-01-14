using GLOW.Core.Data.Data;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public static class UserArtworkFragmentDataTranslator
    {
        public static UserArtworkFragmentModel ToUserArtworkFragmentModel(UsrArtworkFragmentData data)
        {
            return new UserArtworkFragmentModel(
                new MasterDataId(data.MstArtworkId),
                new MasterDataId(data.MstArtworkFragmentId)
            );
        }
    }
}
