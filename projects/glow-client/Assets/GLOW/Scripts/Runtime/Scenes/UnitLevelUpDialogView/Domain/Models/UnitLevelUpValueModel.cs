using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitLevelUpDialogView.Domain.ValueObjects;

namespace GLOW.Scenes.UnitLevelUpDialogView.Domain.Models
{
    public record UnitLevelUpValueModel(
        UnitLevel Level,
        Coin ConsumeCoinValue,
        Coin ConsumedCoin,
        HP AfterHP,
        AttackPower AfterAttackPower,
        SpecialAttackName SpecialAttackName,
        SpecialAttackInfoDescription SpecialAttackDescription,
        LevelUpButtonState ButtonState);
}
