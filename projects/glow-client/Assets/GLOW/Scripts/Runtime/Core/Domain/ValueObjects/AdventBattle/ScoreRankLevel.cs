using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record ScoreRankLevel(ObscuredInt Value)
    {
        static ScoreRankLevel MinLevel { get; } = new(0);
        static ScoreRankLevel MaxLevel { get; } = new(4);

        public bool IsValid()
        {
            return MinLevel.Value <= Value && Value <= MaxLevel.Value;
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
