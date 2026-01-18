using GLOW.Core.Domain.ValueObjects.AdventBattle;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpRankLevel(ObscuredInt Value)
    {
        public static PvpRankLevel Empty { get; } = new PvpRankLevel(0);
        public static PvpRankLevel One { get; } = new PvpRankLevel(1);

        static PvpRankLevel MinLevel { get; } = new(0);

        static PvpRankLevel MaxLevel { get; } = new(4);

        public static bool operator >(PvpRankLevel a, PvpRankLevel b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(PvpRankLevel a, PvpRankLevel b)
        {
            return a.Value < b.Value;
        }

        public bool IsMaxLevel()
        {
            return Value == MaxLevel.Value;
        }

        public ScoreRankLevel ToScoreRankLevel()
        {
            return new ScoreRankLevel(Value);
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public bool IsValid()
        {
            return MinLevel.Value <= Value && Value <= MaxLevel.Value;
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
