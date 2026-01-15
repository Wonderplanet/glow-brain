using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
namespace GLOW.Core.Domain.Extensions
{
    public static class RankTypeExtensions
    {
        public static string ToDisplayString(this RankType rankType)
        {
            return rankType switch
            {
                RankType.Bronze => "ブロンズ",
                RankType.Silver => "シルバー",
                RankType.Gold => "ゴールド",
                RankType.Master => "マスター",
                _ => string.Empty,
            };
        }

        public static string ToDisplayStringWithRankLevel(this RankType rankType, AdventBattleScoreRankLevel rankLevel)
        {
            return ZString.Format("{0}{1}", rankType.ToDisplayString(), rankLevel.Value);
        }
    }
}