using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    /// <summary>
    /// ガチャを引く回数
    /// </summary>
    public record GachaDrawCount(ObscuredInt Value) : IGachaCountableValueObject
    {
        /// <summary>
        /// 一度にガチャを引ける上限
        /// </summary>
        public static GachaDrawCount MaxGachaDrawCount { get; } = new(10);
        public static GachaDrawCount Zero { get; } = new(0);
        public static GachaDrawCount Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static bool operator <(GachaDrawCount a, GachaDrawCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(GachaDrawCount a, GachaDrawCount b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(GachaDrawCount a, GachaDrawCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(GachaDrawCount a, GachaDrawCount b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(GachaDrawCount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <=(GachaDrawCount a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >(GachaDrawCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >=(GachaDrawCount a, int b)
        {
            return a.Value >= b;
        }

        public bool IsSingleDraw()
        {
            return Value == 1;
        }
    }
}
