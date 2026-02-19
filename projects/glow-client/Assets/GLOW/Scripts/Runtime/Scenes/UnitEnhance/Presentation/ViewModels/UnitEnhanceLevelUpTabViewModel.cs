using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhance.Domain.Models;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceLevelUpTabViewModel(
        CharacterUnitRoleType RoleType,
        UnitEnhanceLevelUpViewModel LevelUp,
        UnitEnhanceRankUpViewModel RankUp,
        HP Hp,
        AttackPower AttackPower,
        UnitGrade UnitGrade,
        NotificationBadge IsGradeUp);
}
