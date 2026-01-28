using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstInGameGimmickObjectModel(
        MasterDataId Id,
        InGameGimmickObjectAssetKey AssetKey)
    {
        public static MstInGameGimmickObjectModel Empty { get; } = new(
            MasterDataId.Empty,
            InGameGimmickObjectAssetKey.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
