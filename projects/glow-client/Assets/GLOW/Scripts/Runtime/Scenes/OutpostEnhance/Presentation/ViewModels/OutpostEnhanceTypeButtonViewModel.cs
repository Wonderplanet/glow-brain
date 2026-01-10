using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.OutpostEnhance.Presentation.ViewModels
{
    public record OutpostEnhanceTypeButtonViewModel(
        MasterDataId Id,
        MasterDataId EnhanceId,
        MasterDataId EnhanceLevelId,
        OutpostEnhanceName Name,
        OutpostEnhanceDescription Description,
        OutpostEnhanceLevel Level,
        OutpostEnhanceLevel MaxLevel,
        Coin Cost,
        OutpostEnhanceIconAssetPath IconAssetPath,
        bool IsMaxLevel,
        bool IsCostSufficient);
}
