using System;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
namespace GLOW.Scenes.PvpRanking.Domain.ValueObjects
{
    public record PvpScoreRankLevel(ObscuredInt Value) : IComparable
    {
        public static PvpScoreRankLevel Empty { get; } = new(0);
        public static PvpScoreRankLevel Zero { get; } = new(0);
        static PvpScoreRankLevel MinLevel { get; } = new(0);
        static PvpScoreRankLevel MaxLevel { get; } = new(4);

        public bool IsValid()
        {
            return MinLevel.Value <= Value && Value <= MaxLevel.Value;
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
            if (obj is PvpScoreRankLevel other)
            {
                return Value.CompareTo(other.Value);
            }
            return -1;
        }
    }
}
