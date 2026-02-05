using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpExcludeRankingFlag(ObscuredBool Value)
    {
        public static PvpExcludeRankingFlag True => new PvpExcludeRankingFlag(true);
        public static PvpExcludeRankingFlag False => new PvpExcludeRankingFlag(false);

        public static implicit operator bool(PvpExcludeRankingFlag flag) => flag.Value;
    }
}
