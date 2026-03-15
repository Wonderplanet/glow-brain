using System.Collections.Generic;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail
{
    public record InGameUnitDetailViewModel(
        InGameUnitDetailInfoViewModel Info,
        InGameUnitDetailSpecialAttackViewModel SpecialAttack,
        IReadOnlyList<UnitEnhanceAbilityViewModel> AbilityList,
        InGameUnitDetailStatusViewModel Status);
}
