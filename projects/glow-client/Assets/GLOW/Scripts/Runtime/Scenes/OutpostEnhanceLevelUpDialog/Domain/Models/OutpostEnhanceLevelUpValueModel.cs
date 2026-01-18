using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.ValueObjects;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.Models
{
    public record OutpostEnhanceLevelUpValueModel(OutpostEnhanceLevel Level, Coin RequiredCoin, Coin ConsumedCoin, OutpostEnhanceLevelUpButtonState ButtonState);
}
