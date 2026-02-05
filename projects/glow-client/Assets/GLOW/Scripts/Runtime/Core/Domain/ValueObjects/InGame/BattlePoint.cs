using System;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// キャラ召喚などで消費するポイント
    /// 0〜最大値
    /// </summary>
    /// <param name="Value"></param>
    public record BattlePoint(ObscuredDecimal Value) : IComparable
    {
        public static BattlePoint Empty { get; } = new(0);
        public static BattlePoint Zero { get; } = new(0);

        public ObscuredDecimal Value { get; } = Math.Clamp(Value, 0, 10000);

        public int CompareTo(object obj)
        {
            if (obj is BattlePoint other)
            {
                return Value.CompareTo(other.Value);
            }

            return -1;
        }

        public static BattlePoint operator +(BattlePoint a, BattlePoint b)
        {
            return new BattlePoint(a.Value + b.Value);
        }

        public static BattlePoint operator -(BattlePoint a, BattlePoint b)
        {
            return new BattlePoint(a.Value - b.Value);
        }

        public static bool operator >(BattlePoint a, BattlePoint b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(BattlePoint a, BattlePoint b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >=(BattlePoint a, BattlePoint b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <=(BattlePoint a, BattlePoint b)
        {
            return a.Value <= b.Value;
        }

        public static BattlePoint Min(BattlePoint a, BattlePoint b)
        {
            return a.Value <= b.Value ? a : b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString("N0", null);
        }

        public SummonCost ToSummonCost()
        {
            return new((int)Value);
        }

        public DeckAutoPlayerSummonPriority ToDeckAutoPlayerSummonPriority()
        {
            return new((int)Value);
        }
    }
}
