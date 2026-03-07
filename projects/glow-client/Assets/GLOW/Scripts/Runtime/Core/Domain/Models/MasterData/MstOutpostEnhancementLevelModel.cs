using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Core.Domain.Models
{
    public record MstOutpostEnhancementLevelModel(
        MasterDataId Id,
        MasterDataId OutpostEnhancementId,
        OutpostEnhanceLevel Level,
        Coin Cost,
        OutpostEnhanceValue EnhanceValue,
        OutpostEnhanceDescription Description)
    {
        public static MstOutpostEnhancementLevelModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            OutpostEnhanceLevel.Empty,
            Coin.Empty,
            OutpostEnhanceValue.Empty,
            OutpostEnhanceDescription.Empty
        );
    }
}
