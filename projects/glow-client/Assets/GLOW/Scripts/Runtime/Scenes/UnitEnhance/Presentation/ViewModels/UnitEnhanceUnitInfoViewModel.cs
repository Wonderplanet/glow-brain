using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceUnitInfoViewModel(
        UnitImageAssetPath UnitImageAssetPath,
        CharacterName Name,
        CharacterUnitRoleType RoleType,
        Rarity Rarity,
        UnitLevel UnitLevel,
        UnitLevel UnitLevelLimit,
        SeriesLogoImagePath SeriesLogoImagePath,
        TickCount SummonCoolTime,
        BattlePoint SummonCost,
        CharacterColor Color,
        UnitEnhanceSpecialAttackViewModel SpecialAttack,
        UnitEnhanceUnitDetailViewModel DetailModel,
        IReadOnlyList<UnitEnhanceAbilityViewModel> AbilityModelList,
        UnitEnhanceUnitStatusViewModel StatusModel);
}
