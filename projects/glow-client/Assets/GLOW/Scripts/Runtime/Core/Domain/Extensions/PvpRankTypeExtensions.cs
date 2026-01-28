using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Core.Domain.Extensions
{
    public static class PvpRankTypeExtensions
    {
        public static string ToDisplayString(this PvpRankClassType rankType)
        {
            return rankType switch
            {
                PvpRankClassType.Bronze => "ブロンズ",
                PvpRankClassType.Silver => "シルバー",
                PvpRankClassType.Gold => "ゴールド",
                PvpRankClassType.Platinum => "プラチナ",
                _ => string.Empty,
            };
        }

        public static string ToDisplayStringWithRankLevel(this PvpRankClassType rankType, PvpRankLevel rankLevel)
        {
            return ZString.Format("{0}{1}", rankType.ToDisplayString(), rankLevel.Value);
        }

        public static string ToDisplayStringWithRankLevel(this PvpRankClassType rankType, ScoreRankLevel scoreRankLevel)
        {
            return ZString.Format("{0}{1}", rankType.ToDisplayString(), scoreRankLevel.Value);
        }
    }
}
