using System;
using System.Globalization;
using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleRaidTotalScore(ObscuredLong Value) : IComparable<AdventBattleRaidTotalScore>
    {
        public static AdventBattleRaidTotalScore Empty { get; } = new(0);
        
        public static AdventBattleRaidTotalScore Zero { get; } = new(0);
        
        public static AdventBattleRaidTotalScore operator -(AdventBattleRaidTotalScore a, AdventBattleRaidTotalScore b)
        {
            return new AdventBattleRaidTotalScore(a.Value - b.Value);
        }
        
        public static bool operator <=(AdventBattleRaidTotalScore a, AdventBattleRaidTotalScore b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >=(AdventBattleRaidTotalScore a, AdventBattleRaidTotalScore b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(AdventBattleRaidTotalScore a, AdventBattleRaidTotalScore b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >(AdventBattleRaidTotalScore a, AdventBattleRaidTotalScore b)
        {
            return a.Value > b.Value;
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public string ToDisplayString()
        {
            if (IsEmpty())
            {
                return "---,---,---,--- pt";
            }
            
            return ZString.Format("{0} pt", Value.ToString("N0", CultureInfo.InvariantCulture));
        }

        public int CompareTo(AdventBattleRaidTotalScore other)
        {
            return Value.CompareTo(other.Value);
        }
    }
}