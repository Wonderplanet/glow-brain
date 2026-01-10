using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitLevelUpDialogView.Domain.ValueObjects;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.ViewModels
{
    public record UnitLevelUpValueViewModel(
        UnitLevel Level,
        Coin ConsumeCoinValue,
        Coin ConsumedCoin,
        HP AfterHP,
        AttackPower AfterAttackPower,
        SpecialAttackName SpecialAttackName,
        SpecialAttackInfoDescription SpecialAttackDescription,
        LevelUpButtonState ButtonState);
}
