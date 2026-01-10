using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Constants
{
    public static class PvpConst
    {
        public const int MatchUserMaxCount = 3; //一度に表示される対戦相手の数
        public static IReadOnlyList<PvpRankClassType> OrderedPvpRankClassTypes { get; } = new List<PvpRankClassType>
        {
            PvpRankClassType.Bronze,
            PvpRankClassType.Silver,
            PvpRankClassType.Gold,
            PvpRankClassType.Platinum,
        };

        public static readonly ContentSeasonSystemId DefaultSysPvpSeasonId = new ContentSeasonSystemId("default_pvp");

        public static readonly (PvpRankClassType rankType, PvpRankLevel rankLevel) MinRankAndLevel = (PvpRankClassType.Bronze, new PvpRankLevel(0));

        public const PvpRankClassType PvpMaxRankClassType = PvpRankClassType.Platinum;
    }
}
