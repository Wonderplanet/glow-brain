using System;
using GLOW.Core.Domain.Constants;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackPowerParameter(AttackPowerParameterType Type, ObscuredFloat Value)
    {
        public static AttackPowerParameter Empty { get; } = new (AttackPowerParameterType.Percentage, 0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public PercentageM ToPercentageM()
        {
            if (IsEmpty()) return PercentageM.Empty;

            if (Type != AttackPowerParameterType.Percentage && Type != AttackPowerParameterType.MaxHpPercentage)
            {
                throw new InvalidOperationException();
            }

            // 小数点以下第3位以降は切り捨て(アウトゲーム側考慮)
            return new PercentageM(Mathf.FloorToInt(Value * 100f) / 100m);
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public AttackPower ToAttackPower()
        {
            if (IsEmpty()) return AttackPower.Empty;
            if (Type != AttackPowerParameterType.Fixed) throw new InvalidOperationException();

            return new AttackPower(Mathf.RoundToInt(Value));
        }
    }
}
