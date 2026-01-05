using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.ValueObjects;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.ViewModels
{
    public record OutpostEnhanceLevelUpValueViewModel(OutpostEnhanceLevel Level, Coin RequiredCoin, Coin ConsumedCoin, OutpostEnhanceLevelUpButtonState ButtonState);
}
