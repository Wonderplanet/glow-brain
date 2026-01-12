using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary> 攻撃側での属性有利時の攻撃ボーナス値 </summary>
    public record CharacterColorAdvantageAttackBonus(ObscuredFloat Value)
    {
        public static CharacterColorAdvantageAttackBonus Empty { get; } = new (0.0f);
        public static CharacterColorAdvantageAttackBonus Default { get; } = new (1.0f);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public decimal ToDecimal()
        {
            return Convert.ToDecimal(Value);
        }
    }
}
