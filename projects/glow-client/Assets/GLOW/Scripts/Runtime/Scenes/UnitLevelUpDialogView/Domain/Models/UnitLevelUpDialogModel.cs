using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitLevelUpDialogView.Domain.Models
{
    public record UnitLevelUpDialogModel(
        CharacterUnitRoleType RoleType,
        PlayerResourceModel IconModel,
        UnitLevel CurrentLevel,
        Coin PossessionCoin,
        HP CurrentHp,
        AttackPower CurrentAttackPower,
        IReadOnlyList<UnitLevelUpValueModel> LevelValues);
}
