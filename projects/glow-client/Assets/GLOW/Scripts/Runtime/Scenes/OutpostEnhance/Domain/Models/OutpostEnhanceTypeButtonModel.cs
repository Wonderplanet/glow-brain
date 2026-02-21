using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.OutpostEnhance.Domain.Models
{
    public record OutpostEnhanceTypeButtonModel(
        MasterDataId Id,
        MasterDataId EnhanceId,
        MasterDataId EnhanceLevelId,
        OutpostEnhanceName Name,
        OutpostEnhanceDescription Description,
        OutpostEnhanceLevel Level,
        OutpostEnhanceLevel MaxLevel,
        Coin Cost,
        OutpostEnhancementType Type,
        OutpostEnhanceIconAssetPath IconAssetPath);
}
