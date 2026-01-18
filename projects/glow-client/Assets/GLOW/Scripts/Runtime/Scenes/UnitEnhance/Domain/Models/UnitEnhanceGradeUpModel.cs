using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceGradeUpModel(
        ItemModel RequireItemIconModel,
        ItemAmount RequireItemAmount,
        ItemAmount PossessionItemAmount,
        ItemName ItemName,
        HP BeforeHp,
        HP AfterHp,
        AttackPower BeforeAttackPower,
        AttackPower AfterAttackPower,
        UnitGrade UnitGrade,
        NotificationBadge IsGradeUp
        );
}
