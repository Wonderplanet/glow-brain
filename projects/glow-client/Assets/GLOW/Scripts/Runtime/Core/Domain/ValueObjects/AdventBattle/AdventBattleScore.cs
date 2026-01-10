using System;
using System.Globalization;
using Cysharp.Text;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleScore(ObscuredLong Value) : IComparable<AdventBattleScore>
    {
        public static AdventBattleScore Empty { get; } = new (0);

        public static AdventBattleScore Zero { get; } = new (0);

        public static AdventBattleScore Infinity { get; } = new (long.MaxValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public bool IsInfinity()
        {
            return ReferenceEquals(this, Infinity);
        }

        public string ToDisplayString()
        {
            if (IsEmpty())
            {
                return "---,---,---,--- pt";
            }

            return ZString.Format("{0} pt", Value.ToString("N0", CultureInfo.InvariantCulture));
        }

        public string ToStringSeparate()
        {
            if (IsEmpty())
            {
                return "---,---,---,---";
            }

            return ZString.Format("{0}", Value.ToString("N0", CultureInfo.InvariantCulture));
        }

        public static AdventBattleScore operator +(AdventBattleScore a, AdventBattleScore b)
        {
            return new AdventBattleScore(a.Value + b.Value);
        }

        public static AdventBattleScore operator -(AdventBattleScore a, AdventBattleScore b)
        {
            return new AdventBattleScore(a.Value - b.Value);
        }

        public static AdventBattleScore operator /(AdventBattleScore a, AdventBattleScore b)
        {
            return new AdventBattleScore(a.Value / b.Value);
        }

        public static bool operator <=(AdventBattleScore a, AdventBattleScore b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >=(AdventBattleScore a, AdventBattleScore b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(AdventBattleScore a, AdventBattleScore b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >(AdventBattleScore a, AdventBattleScore b)
        {
            return a.Value > b.Value;
        }

        public static AdventBattleScore Min(AdventBattleScore a, AdventBattleScore b)
        {
            return a.Value < b.Value ? a : b;
        }

        public int CompareTo(AdventBattleScore other) => Value.CompareTo(other.Value);

        public InGameScore ToInGameScore => new InGameScore(Value);
    }
}
