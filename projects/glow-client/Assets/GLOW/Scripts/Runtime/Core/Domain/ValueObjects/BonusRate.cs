using System;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record BonusRate(ObscuredFloat Value) : IComparable
    {
        public static BonusRate Empty { get; } = new(0);

        public EventBonusPercentage ToEventBonusPercentage()
        {
            return new(Mathf.RoundToInt(Value * 100));
        }

        public int CompareTo(object obj)
        {
            if (obj is BonusRate other)
            {
                return Value.CompareTo(other.Value);
            }

            return 0;
        }
    }
}
