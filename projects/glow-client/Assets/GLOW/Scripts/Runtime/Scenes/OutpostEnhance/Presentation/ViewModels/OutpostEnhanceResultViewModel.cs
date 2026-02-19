using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.OutpostEnhance.Presentation.ViewModels
{
    public record OutpostEnhanceResultViewModel(
        OutpostEnhanceName Name,
        OutpostEnhanceLevel BeforeLevel,
        OutpostEnhanceLevel AfterLevel,
        OutpostEnhanceIconAssetPath IconAssetPath,
        bool IsMaxLevel);
}
