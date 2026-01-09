using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class UserEnemyDiscoverDataTranslator
    {
        public static UserEnemyDiscoverModel Translate(UsrEnemyDiscoveryData data)
        {
            return new UserEnemyDiscoverModel(
                new MasterDataId(data.MstEnemyCharacterId),
                new NewEncyclopediaFlag(data.IsNewEncyclopedia));
        }
    }
}
