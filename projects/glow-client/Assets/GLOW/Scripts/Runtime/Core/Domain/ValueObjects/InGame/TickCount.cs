using System;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// ゲームループの更新カウント
    /// マイナス不可
    /// </summary>
    /// <param name="Value"></param>
    public record TickCount(ObscuredLong Value) : IComparable<TickCount>
    {
        public const int TickCountPerSec = 50;

        public static TickCount Empty { get; } = new(0);
        public static TickCount Infinity { get; } = new(long.MaxValue);
        public static TickCount Zero { get; } = new(0);
        public static TickCount One { get; } = new(1);

        public ObscuredLong Value { get; } = Value > 0 ? Value : 0;

        public static TickCount FromSeconds(float seconds)
        {
            return new TickCount(Mathf.FloorToInt(seconds * TickCountPerSec));
        }

        public static TickCount FromSeconds(int seconds)
        {
            return new TickCount(seconds * TickCountPerSec);
        }

        public static TickCount operator +(TickCount a, TickCount b)
        {
            if (ReferenceEquals(a, Infinity) || ReferenceEquals(b, Infinity))
            {
                return Infinity;
            }

            return new TickCount(a.Value + b.Value);
        }

        public static TickCount operator -(TickCount a, TickCount b)
        {
            if (ReferenceEquals(a, Infinity))
            {
                return Infinity;
            }

            if (a.Value == 0)
            {
                return Zero;
            }

            return new TickCount(a.Value - b.Value);
        }

        public static float operator /(TickCount a, TickCount b)
        {
            return (float)a.Value / b.Value;
        }

        public static TickCount operator *(TickCount a, float b)
        {
            if (ReferenceEquals(a, Infinity))
            {
                return Infinity;
            }

            return new TickCount(Mathf.FloorToInt(a.Value * b));
        }

        public static float operator *(float a, TickCount b)
        {
            if (ReferenceEquals(b, Infinity))
            {
                return float.MaxValue;
            }

            return a * b.Value;
        }

        public static float operator /(float a, TickCount b)
        {
            if (ReferenceEquals(b, Infinity))
            {
                return 0f;
            }

            return a / b.Value;
        }

        public static bool operator <(TickCount a, TickCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(TickCount a, TickCount b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(TickCount a, TickCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(TickCount a, TickCount b)
        {
            return a.Value >= b.Value;
        }

        public static TickCount Max(TickCount a, TickCount b)
        {
            return a.Value >= b.Value ? a : b;
        }

        public static TickCount Min(TickCount a, TickCount b)
        {
            return a.Value <= b.Value ? a : b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsInfinity()
        {
            return ReferenceEquals(this, Infinity);
        }

        public bool IsZero() => Value == 0;

        public long ToMilliSeconds()
        {
            return Value * 20;
        }

        public float ToSeconds()
        {
            return Value / (float)TickCountPerSec;
        }

        public string ToSecondsString()
        {
            return ToSeconds().ToString("F2");
        }

        public StageClearTime ToStageClearTime()
        {
            return new StageClearTime(TimeSpan.FromSeconds(ToSeconds()));
        }
        
        public SpecialAttackCoolTime ToSpecialAttackCoolTime()
        {
            return new SpecialAttackCoolTime(this);
        }

        public int CompareTo(TickCount other)
        {
            return Value.CompareTo(other.Value);
        }
    }
}
