using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary> 防御側での属性有利時の防御ボーナス値 </summary>
    public record CharacterColorAdvantageDefenseBonus(ObscuredFloat Value)
    {
        public static CharacterColorAdvantageDefenseBonus Empty { get; } = new (0.0f);
        public static CharacterColorAdvantageDefenseBonus Default { get; } = new (1.0f);

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
