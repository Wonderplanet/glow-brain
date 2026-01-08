using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstEnemyOutpostModel(
        MasterDataId Id,
        HP Hp,
        OutpostAssetKey OutpostAssetKey,
        ArtworkAssetKey ArtworkAssetKey,
        OutpostDamageInvalidationFlag IsDamageInvalidation)
    {
        public static MstEnemyOutpostModel Empty { get; } = new(
            MasterDataId.Empty,
            HP.Empty,
            OutpostAssetKey.Empty,
            ArtworkAssetKey.Empty,
            OutpostDamageInvalidationFlag.False);
    }
}
