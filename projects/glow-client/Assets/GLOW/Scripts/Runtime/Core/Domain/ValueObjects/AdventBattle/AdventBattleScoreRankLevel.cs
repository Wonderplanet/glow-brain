using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleScoreRankLevel(ObscuredInt Value) : IComparable
    {
        public static AdventBattleScoreRankLevel Empty { get; } = new(0);

        public static AdventBattleScoreRankLevel Zero { get; } = new(0);

        static AdventBattleScoreRankLevel MinLevel { get; } = new(0);

        static AdventBattleScoreRankLevel MaxLevel { get; } = new(4);

        public static bool operator >(AdventBattleScoreRankLevel a, AdventBattleScoreRankLevel b)
        {
            return a.Value > b.Value;
        }
        
        public static bool operator <(AdventBattleScoreRankLevel a, AdventBattleScoreRankLevel b)
        {
            return a.Value < b.Value;
        }
        
        public bool IsValid()
        {
            return MinLevel.Value <= Value && Value <= MaxLevel.Value;
        }
        
        public bool IsMaxLevel()
        {
            return Value == MaxLevel.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public int CompareTo(object obj)
        {
            if (obj is EventBonusPercentage other)
            {
                return Value.CompareTo(other.Value);
            }
            return -1;
        }

        public ScoreRankLevel ToScoreRankLevel()
        {
            return new ScoreRankLevel(Value);
        }
    }
}
