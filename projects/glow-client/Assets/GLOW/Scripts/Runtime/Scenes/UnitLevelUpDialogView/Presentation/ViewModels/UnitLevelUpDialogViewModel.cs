using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.ViewModels
{
    public record UnitLevelUpDialogViewModel(
        CharacterUnitRoleType RoleType,
        PlayerResourceIconViewModel IconViewModel,
        UnitLevel CurrentLevel,
        Coin PossessionCoin,
        HP CurrentHp,
        AttackPower CurrentAttackPower,
        IReadOnlyList<UnitLevelUpValueViewModel> LevelValues);
}
