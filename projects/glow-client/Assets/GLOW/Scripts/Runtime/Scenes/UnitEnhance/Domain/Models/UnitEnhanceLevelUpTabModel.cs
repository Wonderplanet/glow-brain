using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceLevelUpTabModel(
        CharacterUnitRoleType RoleType,
        UnitEnhanceLevelUpModel LevelUp,
        UnitEnhanceRankUpModel RankUp,
        HP Hp,
        AttackPower AttackPower,
        UnitGrade UnitGrade,
        NotificationBadge IsGradeUp);
}
