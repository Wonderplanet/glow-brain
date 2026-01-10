using System;
using System.Globalization;
using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpRankingRank(ObscuredInt Value) : IComparable
    {
        public static PvpRankingRank Empty { get; } = new(0);
        public static PvpRankingRank One { get; } = new(1);
        public static PvpRankingRank Infinity { get; } = new(int.MaxValue);

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

        public bool IsFirstRank()
        {
            return Value == 1;
        }

        public bool IsSecondRank()
        {
            return Value == 2;
        }

        public bool IsThirdRank()
        {
            return Value == 3;
        }

        public bool IsLowerFourth()
        {
            return Value >= 4;
        }

        public static PvpRankingRank operator -(PvpRankingRank a, PvpRankingRank b)
        {
            return new PvpRankingRank(a.Value - b.Value);
        }
        
        public static PvpRankingRank operator +(PvpRankingRank a, PvpRankingRank b)
        {
            return new PvpRankingRank(a.Value + b.Value);
        }

        public static PvpRankingRank operator /(PvpRankingRank a, PvpRankingRank b)
        {
            return new PvpRankingRank(a.Value / b.Value);
        }

        public static bool operator <=(PvpRankingRank a, PvpRankingRank b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >=(PvpRankingRank a, PvpRankingRank b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(PvpRankingRank a, PvpRankingRank b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >(PvpRankingRank a, PvpRankingRank b)
        {
            return a.Value > b.Value;
        }

        public string ToDisplayString()
        {
            return ZString.Format("{0}", Value.ToString("N0", CultureInfo.InvariantCulture));
        }

        public int CompareTo(object obj)
        {
            if (obj is PvpRankingRank other)
            {
                return Value.CompareTo(other.Value);
            }
            return 0;
        }
    }
}