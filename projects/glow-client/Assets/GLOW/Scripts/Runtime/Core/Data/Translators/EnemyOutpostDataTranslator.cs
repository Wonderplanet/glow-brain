using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class EnemyOutpostDataTranslator
    {
        public static MstEnemyOutpostModel ToEnemyOutpostModel(MstEnemyOutpostData mstEnemyOutpostData)
        {
            return new MstEnemyOutpostModel(
                new MasterDataId(mstEnemyOutpostData.Id),
                new HP(mstEnemyOutpostData.Hp),
                string.IsNullOrEmpty(mstEnemyOutpostData.OutpostAssetKey)
                    ? OutpostAssetKey.Empty
                    : new OutpostAssetKey(mstEnemyOutpostData.OutpostAssetKey),
                string.IsNullOrEmpty(mstEnemyOutpostData.ArtworkAssetKey)
                    ? ArtworkAssetKey.Empty
                    : new ArtworkAssetKey(mstEnemyOutpostData.ArtworkAssetKey),
                new OutpostDamageInvalidationFlag(mstEnemyOutpostData.IsDamageInvalidation));
        }
    }
}
