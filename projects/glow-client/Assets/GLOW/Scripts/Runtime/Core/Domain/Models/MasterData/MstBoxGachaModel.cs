using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Models
{
    public record MstBoxGachaModel(
        MasterDataId Id,
        MasterDataId MstEventId,
        MasterDataId CostId,
        CostAmount CostAmount,
        BoxGachaLoopType BoxGachaLoopType,
        BoxGachaName Name,
        KomaBackgroundAssetKey KomaBackgroundAssetKey,
        MasterDataId MstDisplayUnitIdFirst,
        MasterDataId MstDisplayUnitIdSecond)
    {
        public static MstBoxGachaModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            CostAmount.Empty,
            BoxGachaLoopType.All,
            BoxGachaName.Empty,
            KomaBackgroundAssetKey.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}