using System;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public enum UnitMoveSpeedType
    {
        None = 0,     // 速度設定なし(スペシャルキャラ)
        VerySlow = 1, // とても遅い
        Slow = 2,     // 遅い
        Normal = 3,   // 普通
        Fast = 4,     // 速い
        VeryFast = 5  // とても速い
    }
    
    public record UnitMoveSpeed(ObscuredFloat Value) : IComparable
    {
        public static UnitMoveSpeed Empty { get; } = new(0);
        public static UnitMoveSpeed LowerLimitWithDebuff { get; } = new(4); // デバフによる移動速度ダウンの最低値

        public static UnitMoveSpeed operator *(UnitMoveSpeed a, PercentageM b)
        {
            return new UnitMoveSpeed(a.Value * (float)b.ToRate());
        }

        public static bool operator > (UnitMoveSpeed a, UnitMoveSpeed b)
        {
            return a.Value > b.Value;
        }

        public static bool operator < (UnitMoveSpeed a, UnitMoveSpeed b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >= (UnitMoveSpeed a, UnitMoveSpeed b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <= (UnitMoveSpeed a, UnitMoveSpeed b)
        {
            return a.Value <= b.Value;
        }

        public static UnitMoveSpeed Max(UnitMoveSpeed a, UnitMoveSpeed b)
        {
            return a.Value >= b.Value ? a : b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public string ToConvertedString()
        {
            return Value switch
            {
                var value when value <= 20 => "とても遅い",
                var value when value <= 29 => "遅い",
                var value when value <= 40 => "普通",
                var value when value <= 50 => "速い",
                _ => "とても速い"
            };
        }
        
        public UnitMoveSpeedType ToConvertedSpeedType()
        {
            return Value switch
            {
                var value when value <= 20 => UnitMoveSpeedType.VerySlow,
                var value when value <= 29 => UnitMoveSpeedType.Slow,
                var value when value <= 40 => UnitMoveSpeedType.Normal,
                var value when value <= 50 => UnitMoveSpeedType.Fast,
                _ => UnitMoveSpeedType.VeryFast
            };
        }

        public int CompareTo(object obj)
        {
            if (obj is UnitMoveSpeed other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
