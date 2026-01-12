using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class AdventBattleRankingResultModelTranslator
    {
        public static AdventBattleRankingResultModel ToAdventBattleRankingModel(AdventBattleRankingResultData data)
        {
            return new AdventBattleRankingResultModel(
                data.Ranking.Select(ToAdventBattleRankingItemModel).ToList(),
                ToAdventBattleMyRankingModel(data.MyRanking));
        }

        static AdventBattleRankingItemModel ToAdventBattleRankingItemModel(AdventBattleRankingItemData data)
        {
            return new AdventBattleRankingItemModel(
                string.IsNullOrEmpty(data.MyId) ? UserMyId.Empty : new UserMyId(data.MyId),
                data.Rank == 0 ? AdventBattleRankingRank.Empty : new AdventBattleRankingRank(data.Rank),
                string.IsNullOrEmpty(data.Name) ? UserName.Empty : new UserName(data.Name),
                string.IsNullOrEmpty(data.MstUnitId) ? MasterDataId.Empty : new MasterDataId(data.MstUnitId),
                string.IsNullOrEmpty(data.MstEmblemId) ? MasterDataId.Empty : new MasterDataId(data.MstEmblemId),
                new AdventBattleScore(data.Score),
                new AdventBattleScore(data.TotalScore));
        }

        public static AdventBattleMyRankingModel ToAdventBattleMyRankingModel(AdventBattleMyRankingData data)
        {
            return new AdventBattleMyRankingModel(
                data.Rank == 0 ? AdventBattleRankingRank.Empty : new AdventBattleRankingRank(data.Rank),
                new AdventBattleScore(data.Score),
                new AdventBattleScore(data.TotalScore),
                new AdventBattleRankingExcludeRankingFlag(data.IsExcludeRanking));
        }
    }
}
