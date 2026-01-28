using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceUnitInfoModel(
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
        UnitEnhanceSpecialAttackModel SpecialAttack,
        UnitEnhanceUnitDetailModel DetailModel,
        IReadOnlyList<UnitEnhanceAbilityModel> AbilityModelList,
        UnitEnhanceUnitStatusModel StatusModel);
}
