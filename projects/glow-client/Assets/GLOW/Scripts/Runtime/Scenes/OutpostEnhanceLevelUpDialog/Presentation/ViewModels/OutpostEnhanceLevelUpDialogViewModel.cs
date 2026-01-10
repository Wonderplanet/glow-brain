using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.ViewModels
{
    public record OutpostEnhanceLevelUpDialogViewModel(OutpostEnhanceLevel CurrentLevel, Coin PossessionCoin, IReadOnlyList<OutpostEnhanceLevelUpValueViewModel> LevelValues);
}
