using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.Models
{
    public record OutpostEnhanceLevelUpDialogModel(OutpostEnhanceLevel CurrentLevel, Coin PossessionCoin, IReadOnlyList<OutpostEnhanceLevelUpValueModel> LevelValues);
}
