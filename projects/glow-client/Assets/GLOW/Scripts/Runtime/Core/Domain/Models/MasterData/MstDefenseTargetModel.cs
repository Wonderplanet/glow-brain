using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstDefenseTargetModel(
        MasterDataId Id,
        DefenseTargetAssetKey AssetKey,
        FieldCoordV2 Position,
        HP HP)
    {
        public static MstDefenseTargetModel Empty { get; } = new MstDefenseTargetModel(
            MasterDataId.Empty,
            DefenseTargetAssetKey.Empty,
            FieldCoordV2.Empty,
            HP.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
