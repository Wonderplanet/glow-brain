using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceGradeUpTabViewModel(
        ItemIconViewModel RequireItemIconViewModel,
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
