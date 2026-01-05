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

        public Percentage ToPercentage()
        {
            if (IsEmpty()) return Percentage.Empty;

            if (Type != AttackPowerParameterType.MaxHpPercentage)
            {
                throw new InvalidOperationException();
            }

            return new Percentage(Mathf.RoundToInt(Value));
        }

        public PercentageM ToPercentageM()
        {
            if (IsEmpty()) return PercentageM.Empty;

            if (Type != AttackPowerParameterType.Percentage)
            {
                throw new InvalidOperationException();
            }

            // 小数点以下第2位で四捨五入
            return new PercentageM(Mathf.RoundToInt(Value * 10f) / 10m);
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
