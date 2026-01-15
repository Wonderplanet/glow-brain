using System;
using Cysharp.Text;
namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleRankingRank(int Value) : IComparable
    {
        public static AdventBattleRankingRank Empty { get; } = new(0);
        
        public static AdventBattleRankingRank One { get; } = new(1);
        
        public static AdventBattleRankingRank Infinity { get; } = new(int.MaxValue);

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

        public static AdventBattleRankingRank operator -(AdventBattleRankingRank a, AdventBattleRankingRank b)
        {
            return new AdventBattleRankingRank(a.Value - b.Value);
        }
        
        public static AdventBattleRankingRank operator +(AdventBattleRankingRank a, AdventBattleRankingRank b)
        {
            return new AdventBattleRankingRank(a.Value + b.Value);
        }

        public static AdventBattleRankingRank operator /(AdventBattleRankingRank a, AdventBattleRankingRank b)
        {
            return new AdventBattleRankingRank(a.Value / b.Value);
        }

        public static bool operator <=(AdventBattleRankingRank a, AdventBattleRankingRank b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >=(AdventBattleRankingRank a, AdventBattleRankingRank b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(AdventBattleRankingRank a, AdventBattleRankingRank b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >(AdventBattleRankingRank a, AdventBattleRankingRank b)
        {
            return a.Value > b.Value;
        }

        public string ToDisplayString()
        {
            return ZString.Format("{0}", Value.ToString("N0"));
        }

        public int CompareTo(object obj)
        {
            if (obj is AdventBattleRankingRank other)
            {
                return Value.CompareTo(other.Value);
            }
            return 0;
        }
    }
}