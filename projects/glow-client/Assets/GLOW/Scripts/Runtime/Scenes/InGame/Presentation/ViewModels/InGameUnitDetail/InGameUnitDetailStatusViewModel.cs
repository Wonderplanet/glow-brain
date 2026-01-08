using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail
{
    public record InGameUnitDetailStatusViewModel(
        CharacterUnitRoleType RoleType,
        HP Hp,
        HP CurrentHp,
        HP DefaultHp,
        AttackPower AttackPower,
        AttackPower DefaultAttackPower,
        CharacterAttackRangeType AttackRange,
        UnitMoveSpeed MoveSpeed,
        UnitMoveSpeed DefaultMoveSpeed,
        IReadOnlyList<InGameUnitDetailBalloonMessage> InGameUnitDetailBalloonMessageList);
}
