using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhance.Domain.Models;

namespace GLOW.Scenes.UnitDetail.Domain.Models
{
    public record UnitDetailModel(
        UnitImageAssetPath UnitImageAssetPath,
        CharacterName Name,
        CharacterUnitRoleType RoleType,
        Rarity Rarity,
        CharacterColor Color,
        UnitLevel UnitLevel,
        UnitLevel UnitLevelLimit,
        SeriesLogoImagePath SeriesLogoImagePath,
        UnitEnhanceSpecialAttackModel SpecialAttack,
        HP Hp,
        AttackPower AttackPower,
        BattlePoint SummonCost,
        CharacterAttackRangeType AttackRange,
        UnitMoveSpeed MoveSpeed,
        AttackCountPerMinute AttackCountPerMinute,
        UnitRank UnitRank,
        UnitGrade UnitGrade,
        TickCount SummonCoolTime,
        IReadOnlyList<UnitEnhanceAbilityModel> Abilities,
        UnitEnhanceUnitDetailModel DetailModel,
        UnitEnhanceUnitStatusModel StatusModel,
        MaxStatusFlag MaxStatusFlag);
}
