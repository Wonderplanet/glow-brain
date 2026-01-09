using GLOW.Core.Domain.Constants;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// スペシャルキャラの火力に関するパラメータ
    /// </summary>
    public record AttackPowerParameterValue(ObscuredFloat Value)
    {
        public static AttackPowerParameterValue Empty { get; } = new(0);

        public static bool operator <(AttackPowerParameterValue a, AttackPowerParameterValue b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(AttackPowerParameterValue a, AttackPowerParameterValue b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(AttackPowerParameterValue a, AttackPowerParameterValue b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(AttackPowerParameterValue a, AttackPowerParameterValue b)
        {
            return a.Value >= b.Value;
        }

        public static AttackPowerParameterValue operator + (AttackPowerParameterValue a, AttackPowerParameterValue b)
        {
            return new AttackPowerParameterValue(a.Value + b.Value);
        }

        public static AttackPowerParameterValue operator - (AttackPowerParameterValue a, AttackPowerParameterValue b)
        {
            return new AttackPowerParameterValue(a.Value - b.Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
